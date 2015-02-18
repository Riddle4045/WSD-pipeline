#include <mpi/mpi.h>
#include <stdio.h>
#include "parallel.h"
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <math.h>
#include <algorithm>
#include <memory.h>
#include <time.h>
#include <sys/stat.h>
#include <flann/flann.hpp>

#define MASTER_RANK 0
#define PROBE_SLEEP 10
#define MAX_NUM_CHUNK 1000
#define NUM_POINTS_TRAINING 2e8

using namespace std;

/*global variables*/
int RANK; //the rank of this process.
double total_system_memory; //in GB.
const int num_thread_per_machine = 12;
double begin_ts;
char outpath[100];
int knnsearch = -1;

char usage_msg [] = "How to use:\n\
\n\
	1) Setup data description file 'datapoints.bin' (binary format): \n\
		   1) The header should contain two 8-bits integers. The first integer is the number of data points, and the second integer is the dimension of each data point.\n\
		   2) The rest of data should contain data points in 8-bits unsigned integer format.\n\
	   \n\
	3) Setup K-means parameter file 'kmeans.conf' (ascii format)\n\
	   		num_clusters 	  N\n\
	   		distance_metric	  code #1 for correlation distance. 2 for cosine distance. 3 for Euclidean distance. \n\
	   		converge_ratio    th #if the cost's change ratio compared with previous iteration is less than this value, the program terminates.\n\
	   		max_iter	  	  it\n\
	   The program terminates when it either reaches max_iter or converge_th. The initial clusters are picked randomly from the input datapoints.\n\
	   \n\
	4) Run the program: mpirun -np num_process --hostfile /path/to/host/file /path/to/mpikmeans /path/to/datapoints/file /path/to/kmeans/configuration/file /path/to/output/folder\n\
			num_process: the number of process should be used to run k-means in parallel.\n\
			hostfile: the configuration file of OpenMPI.\n\
			The mpikmeans outputs will be written to /path/to/output/folder. Make sure to have enough space if the data is very large.\n\
			\n\
	5) Setup NFS: all the data files, configuration files, and executable files must be placed under folders that are accessible by all machines via Network File System.\n\
			\n\
	6) Output: \n\
			1) The program saves the clustering results at every iteration.\n\
			2) It provides clustering allocation for each data point in the output file 'mpikmeans.alloc'.\n\
					The file is in binary format.\n\
					The data type is 64-bits unsigned int.\n\
			3) It provides clustering centres in the output file 'mpikmeans.cents'.\n\
					The file is in binary format.\n\
					The data type is 32-bits float.	\n\
			4) It provides number of samples falling into each cluster in the output file 'mpikmeans.count'.\n\
					The file is in binary format.\n\
					The data type is 64-bits int.	\n\
			5) It provides a description file 'mpikmeans.desc' (ascii format):\n\
					converged_cost float_value #average distance from data points to their centriods.\n\
					num_iter integer_value\n\
					time_elapsed float_value #measured in seconds";


void force_exit(const char* msg = "");

/*global struct definitions*/
typedef long long int int64;
typedef unsigned char uchar;
typedef unsigned short ushort;

template<class T_DATABASE>
class KDTREE{
	flann::SearchParams sp;
	flann::KDTreeIndexParams ip;
	flann::Index<flann::L2<T_DATABASE> >* index;
	int nrow, ncol; //size of the dataset based on which the KD tree was built.
public:
	inline KDTREE():ip(5){ //the number of randomized trees.
		index = NULL;
		/*setting SearchParams*/
		sp.checks = 100;  //specifies the maximum leafs to visit when searching for neighbours
		//eps; Search for eps-approximate neighbors. 
		//sorted; Used only by radius search, specifies if the neighbors returned should be sorted by distance
		//max_neighbors; //Specifies the maximum number of neighbors radiu search should return (default: -1 = unlimited). Only used for radius search.
		sp.cores = 1; //How many cores to assign to the search (specify 0 for automatic core selection).
	}
    inline ~KDTREE()
    {
    	if(index){
		    free(index);
		    index = NULL;
        }
    };
	
	inline void Build(T_DATABASE* data, int nrow, int ncol){
		this->nrow = nrow;
		this->ncol = ncol;
		flann::Matrix<T_DATABASE> dataset (data, nrow, ncol);
		if(index) free(index);
		index = new flann::Index< flann::L2<T_DATABASE> >(dataset, ip);
		index->buildIndex();
	}
	
	/* SEARCH: Perform nearest neighbor search.
	 * keys[In]: nb_key x ncol matrix, each row containing one sample to be queried.
	 * nb_key[In]: the number of keys to be queried.
	 * dist[Out]: nb_key x 1 matrix, with each element represents the distance to nearest neighbor.
	 * indice[Out]: nb_key x 1 matrix, with each element represents the index of the nearest neighbor in the dataset based on which KD tree was built.
	*/
	inline void Search(T_DATABASE* keys, const int& nb_key, float* dist, int* indice){
		flann::Matrix<T_DATABASE> queries (keys, nb_key, this->ncol);
		flann::Matrix<int> indices(indice, nb_key, 1);
		flann::Matrix<float> dists(dist, nb_key, 1);
		index->knnSearch(queries, indices, dists, 1, sp);
	}
};

/*This is a chunk of data loaded from data.bin file*/
typedef struct{
	int64 dimension; //dimension of the data points.
	int64 first; //ID of the first data point in this chunk.
	int64 last; //ID of the last data point in this chunk.
	int64 num_points; //total number of points in the entire dataset.
	int num_clusters,max_iteration;
	float converge_ratio;
	int iter; //current iteration, starting at 0.
}DATA;

typedef struct COMP_CHUNK_THREAD_PARAM{
	int* membership;
	float* means;
	int64* counts;
	uchar* chunk_data_part;
	float* centres;
	KDTREE<uchar>* kdt;
	float* dist;
	int* indice;
	int num_points; //number of points in chunk_data_part, which is part of chunk.
	int dimension;
	int num_clusters;
	double cost;
	float* sq_cdata;
	float* sq_cents;
}COMP_CHUNK_THREAD_PARAM;

/*This struct used by worker machines to send results back to the master*/
typedef struct{
	int num_chunk; //the number of chunks.
	double cost; //total cost from all chunks.
	int first[MAX_NUM_CHUNK]; //beginning id of data point for this chunk.
	int last[MAX_NUM_CHUNK]; //ending id of data point for this chunk.
}KMEANS_SPEC;


/*global functions*/
size_t getTotalSystemMemory()
{
    long pages = sysconf(_SC_PHYS_PAGES);
    long page_size = sysconf(_SC_PAGE_SIZE);
    return pages * page_size;
}
void force_exit(const char* msg){
	printf("\n[Error] ");
	puts(msg);
    MPI_Finalize();
    exit(-1);
}
double tic() {
    begin_ts = MPI_Wtime();
    return begin_ts;
}

double toc() {
    return MPI_Wtime() - begin_ts;
}


/*LOAD: load 'data.bin', and 'kmeans.conf'. Also load 'kmeans.alloc' and 'kmeans.desc' if necessary.
* part[In]: which part of data should be loaded. part = 0,1,...,nb_machines-1.
* Return: DATA* success, 0 otherwise.
*/
DATA* load(int part){
	FILE* fp = 0;
	char buf[100];
	DATA* data = new DATA;

	//read header from data.bin
	fp = fopen("user_data/data.bin", "rb");
	if(!fp) force_exit("Failed to open file data.bin");
	fseek(fp, 0L, SEEK_END);
	int64 sz = ftell(fp);
	fseek(fp, 0L, SEEK_SET);
	data->num_points = sz/128, data->dimension = 128;
	
	/*
	data->datapoints = new uchar [data->dimension*data->num_points];
	if (fread(data->datapoints, sizeof (int), data->num_points, fp) != data->num_points){
		force_exit("The file 'data.desc' is corrupted. Expected: dimension speficication.");
	}*/
	fclose(fp);
	
	//kmeans.conf
	fp = fopen("user_data/kmeans.conf", "r");
	if(!fp) force_exit("Failed to load kmeans.conf");
	fscanf(fp, "%s %d\n", buf, &data->num_clusters); if(strcmp(buf,"num_clusters")){force_exit("The file 'kmeans.conf' is corrupted. Expected: num_clusters speficication.");} 
	fscanf(fp, "%s %f\n", buf, &data->converge_ratio); if(strcmp(buf,"converge_ratio")){force_exit("The file 'kmeans.conf' is corrupted. Expected: converge_ratio speficication.");} 
	fscanf(fp, "%s %d\n", buf, &data->max_iteration); if(strcmp(buf,"max_iteration")){force_exit("The file 'kmeans.conf' is corrupted. Expected: max_iteration speficication.");} 
	fclose(fp);
	
	return data;
}

void generate_initial_centres(int num_clusters){
	FILE* fp = fopen("user_data/data.bin", "rb");
	if(!fp) force_exit("Failed to open file data.bin");
	
	fseek(fp, 0L, SEEK_END);
	int64 sz = ftell(fp);
	fseek(fp, 0L, SEEK_SET);
	
	int64 num_points = sz/128, dimension = 128;
	
	
	if(num_points<num_clusters)
		force_exit("num_points<num_clusters");
	/*Load N points to be selected as centres*/
	int N = 10*num_clusters;
	if(N>num_points) N = num_points;
	uchar* candidates = (uchar*) malloc(N*dimension*sizeof(uchar));
	if (fread(candidates, sizeof (uchar), N*dimension, fp) != N*dimension)
		force_exit("The file 'data.bin' is corrupted.");
	fclose(fp);
	/*Randomly select some points as initial centres*/
	srand (time(NULL));
	int* perm = (int*) malloc(N*sizeof(int));
	float* centres = (float*) malloc(num_clusters*dimension*sizeof(float));
  	for (int i=0; i<N; i++) perm[i] = i;
  	random_shuffle (perm, perm+N);
  	for(int i = 0;i<num_clusters;i++)
  		for(int j = 0;j<dimension;j++)
  			centres[i*dimension+j] = candidates[perm[i]*dimension+j];
  	free(perm);
  	free(candidates);
  	/*save centres*/
  	char name [100];
  	sprintf(name,"%s/mpikmeans.cents",outpath);
  	fp = fopen(name, "wb+");
  	if(!fp) force_exit(("Cannot open the file "+string(name)+" for writing.").c_str());
  	if(fwrite (centres, sizeof(float), num_clusters*dimension, fp)!=num_clusters*dimension){
  		force_exit(("generate_initial_centres: Cannot write "+string(name)+" successfully.").c_str());
  	}
  	free(centres);
  	fclose(fp);
}



void thread_comp_chunk(void* par){
	
	COMP_CHUNK_THREAD_PARAM* param = (COMP_CHUNK_THREAD_PARAM*)par;
	float*means = param->means; //out
	float*centres = param->centres;
	int*membership = param->membership; //out
	int64*counts = param->counts; //out
	int num_points = param->num_points;
	int num_clusters = param->num_clusters;
	int dimension = param->dimension;
	uchar*chunk_data_part = param->chunk_data_part;
	memset(means,0,sizeof(float)*dimension*num_clusters);
	memset(counts,0,sizeof(int64)*num_clusters);
	int i,j,k;
	double maxval, cval;
	int maxind;
	uchar* cdata = 0;
	float* pmean = 0;
	float* pcent = 0;
	param->cost  = 0;
	float* sq_cdata = param->sq_cdata;
	float* sq_cents = param->sq_cents;
	int* indice = param->indice;
	float* dist = param->dist;
	if(knnsearch==2){  //kdtree approximate search.
		param->kdt->Search(chunk_data_part, num_points, dist, indice);
		for(i=0;i<num_points;i++){
			cdata = chunk_data_part + i*dimension;
			pmean = means + indice[i]*dimension;
			membership[i] = indice[i];
			counts[indice[i]]++;
			for(k=0;k<dimension;k++)
				pmean[k] += cdata[k];
			param->cost += sqrt(dist[i]);
		}
	}
	else{
		for(i=0;i<num_points;i++){
			cdata = chunk_data_part + i*dimension;
			for(k=0;k<dimension;k++)
				sq_cdata[i] += cdata[k]*cdata[k];
			sq_cdata[i] /= 2;
		}
	
		for(i=0;i<num_clusters;i++){
			pcent = centres + i*dimension;
			for(k=0;k<dimension;k++)
				sq_cents[i] += pcent[k]*pcent[k];
			sq_cents[i] /= 2;
		}
	
		for(i=0;i<num_points;i++){
			cdata = chunk_data_part + i*dimension;
			maxval = (sq_cdata[i] + sq_cents[0]);
			maxind = 0;
			for(k=0;k<dimension;k++)
				maxval -= cdata[k]*centres[k];
			for(j=1;j<num_clusters;j++){
				pcent = centres + j*dimension;
				cval = sq_cdata[i] + sq_cents[j];
				for(k=0;k<dimension;k++)
					cval -= cdata[k]*pcent[k];
				if(cval<maxval){
					maxval = cval;
					maxind = j;
				}
			}
			pmean = means + maxind*dimension;
			membership[i] = maxind;
			counts[maxind]++;
			for(k=0;k<dimension;k++)
				pmean[k] += cdata[k];
			maxval = sqrt(maxval*2);
			param->cost += maxval;
		}
	}
}

void worker_compute_assignment(double size_memory_in_gb){
	DATA data;
	int dataWaitingFlag;
	int nthreads = num_thread_per_machine;
	MPI_Status Stat;
	static float* centres = 0;
	static uchar* chunk_data = 0;
	static int* membership = 0;
	static float* means = 0;
	static int64* counts = 0;
	static float* sq_cdata = 0;
	static float* sq_cents = 0;
	static float* dist = 0;
	static int* indice = 0;
	static uchar* cent_uchar = 0;
	
  	char name [100];
  	COMP_CHUNK_THREAD_PARAM* params = new COMP_CHUNK_THREAD_PARAM [nthreads];
  	KDTREE<uchar>* kdt = 0;
  	static int flag_load_chunk_data = true;
	//wait for message from master to initialize.
    while (1) {
        /*check whether there is any assignment from master*/
        MPI_Iprobe(MASTER_RANK, MPI_ANY_TAG, MPI_COMM_WORLD, &dataWaitingFlag, &Stat);
        if (dataWaitingFlag) { //Receive a packet
            /*Extract the packet*/
            MPI_Recv(&data, sizeof(DATA), MPI_CHAR, Stat.MPI_SOURCE, MPI_ANY_TAG, MPI_COMM_WORLD, &Stat);
            if(data.first<0){ // kmeans converged.
            	free(centres);
            	free(chunk_data);
            	free(membership);
            	free(means);
            	free(counts);
            	free(sq_cdata);
            	free(sq_cents);
            	if(cent_uchar)
            		free(cent_uchar);
            	if(dist)
            		free(dist);
            	if(indice)
            		free(indice);
        		if(kdt)
        			delete kdt;
            	delete params;
            	break; 
            }
            /*begin computation chunk by chunk*/
            int64 max_num_points_per_chunk = \
            	(size_memory_in_gb*(1e9) - (data.dimension*data.num_clusters*sizeof(float)+sizeof(float)*nthreads*data.dimension*data.num_clusters+sizeof(int64)*nthreads*data.num_clusters+ \
				sizeof(float)*data.num_points+sizeof(float)*data.num_clusters*nthreads+sizeof(float)*data.num_points+sizeof(int)*data.num_points))/(data.dimension*sizeof(uchar));
            int min_num_chunks = ceil((data.last-data.first+1)/(double)max_num_points_per_chunk);
            int64 actual_num_points_per_chunk = 0;
            if(min_num_chunks==1)
            	actual_num_points_per_chunk = data.last - data.first + 1;
            else
            	actual_num_points_per_chunk = max_num_points_per_chunk;
            KMEANS_SPEC ret;
            ret.num_chunk = min_num_chunks;
            if(min_num_chunks>MAX_NUM_CHUNK)
            	force_exit("min_num_chunks>MAX_NUM_CHUNK");
            /*load globally shared centres*/	
            sprintf(name,"%s/mpikmeans.cents",outpath);
            FILE* fp = fopen(name,"rb");
            if(!fp) force_exit(("worker: Cannot open the file "+string(name)+" for reading.").c_str());
            if(!centres)
				centres = (float*) malloc(data.dimension*data.num_clusters*sizeof(float));
			if(!centres) force_exit("Failed to allocate memory for centres.");
			if (fread(centres, sizeof (float), data.dimension*data.num_clusters, fp) != data.dimension*data.num_clusters)
				force_exit(("The file "+string(name)+" is corrupted.").c_str());
			fclose(fp);
			/*compute each chunk*/
			if(!chunk_data)
				chunk_data = (uchar*) malloc(data.dimension*actual_num_points_per_chunk*sizeof(uchar));
			if(!chunk_data) force_exit("Failed to allocate memory for chunk_data.");
			if(!membership)
				membership = (int*) malloc(sizeof(int)*actual_num_points_per_chunk);
			if(!membership) force_exit("Failed to allocate memory for membership.");
			if(!means)
				means = (float*) malloc(sizeof(float)*nthreads*data.dimension*data.num_clusters);
			if(!means) force_exit("Failed to allocate memory for means.");
			if(!counts)
				counts  = (int64*) malloc(sizeof(int64)*nthreads*data.num_clusters);
			if(!counts) force_exit("Failed to allocate memory for counts.");
			if(!sq_cdata)
				sq_cdata = (float*)malloc(sizeof(float)*data.num_points);
			if(!sq_cdata) force_exit("Failed to allocate memory for sq_cdata.");
			if(!sq_cents)
				sq_cents = (float*)malloc(sizeof(float)*data.num_clusters*nthreads);
			if(!sq_cents) force_exit("Failed to allocate memory for sq_cents.");
			if(!dist && knnsearch==2){
				dist = (float*)malloc(sizeof(float)*data.num_points);
				if(!dist) force_exit("Failed to allocate memory for dist.");
			}
			if(!indice && knnsearch==2){
				indice = (int*)malloc(sizeof(int)*data.num_points);
				if(!indice) force_exit("Failed to allocate memory for indice.");
			}
			if(!cent_uchar && knnsearch==2){
				cent_uchar = (uchar*)malloc(sizeof(uchar)*data.dimension*data.num_clusters);
				if(!cent_uchar) force_exit("Failed to allocate memory for cent_uchar.");
			}
			int i,j,k,l,m;
			
			/*build kdtree if necessary*/
			if(knnsearch==2){
				for(i=0;i<data.dimension*data.num_clusters;i++) cent_uchar[i] = centres[i];
				kdt = new KDTREE<uchar>;
				kdt->Build(cent_uchar,data.num_clusters,data.dimension);
			}
			
			int64* pcount;
			float* pmean = 0;
			ret.cost = 0;
            for(i = 0;i<min_num_chunks;i++){ //for each chunk.
            	if(min_num_chunks>1 || flag_load_chunk_data){  //reload the chunk data only if min_num_chunks>1 || it has not yet been loaded before.
		        	/*load corresponding data points from data.bin*/
		        	fp = fopen("user_data/data.bin","rb");
		        	if(!fp) force_exit("Failed to open 'user_data/data.bin' for reading.");
		        	fseek(fp, (data.first+i*actual_num_points_per_chunk)*data.dimension, SEEK_SET);
		        	if(i<min_num_chunks-1){
						if (fread(chunk_data, sizeof (uchar), data.dimension*actual_num_points_per_chunk, fp) != data.dimension*actual_num_points_per_chunk)
							force_exit("'user_data/data.bin' is corrupted.");
					}
					else{ //the last chunk
						actual_num_points_per_chunk = (data.last - data.first + 1) % max_num_points_per_chunk;
						if (fread(chunk_data, sizeof (uchar), data.dimension*actual_num_points_per_chunk, fp) != data.dimension*actual_num_points_per_chunk)
							force_exit("'user_data/data.bin' is corrupted.");
					}
					fclose(fp);
					flag_load_chunk_data = false;
				}
				ret.first[i] = data.first;
				ret.last[i] = data.first + actual_num_points_per_chunk - 1;
            	/*compute this chunk in parallel pthread*/
				Parallel PL;
				vector<void*> thread_params;
				int nb_item_per_thread = ceil(actual_num_points_per_chunk/nthreads);
				memset(sq_cdata,0,sizeof(float)*data.num_points);
				memset(sq_cents,0,sizeof(float)*data.num_clusters*nthreads);
				for (k = 0; k < nthreads; k++){
					params[k].num_clusters = data.num_clusters;
					params[k].dimension = data.dimension;
					params[k].membership = membership + k*nb_item_per_thread;
					params[k].means = means + k*data.dimension*data.num_clusters;
					params[k].counts = counts + k*data.num_clusters;
					params[k].chunk_data_part = chunk_data + k*data.dimension*nb_item_per_thread;
					params[k].num_points = nb_item_per_thread;
					params[k].centres = centres;
					params[k].sq_cdata = sq_cdata + k*nb_item_per_thread;
					params[k].sq_cents = sq_cents + k*data.num_clusters;
					params[k].kdt = kdt;
					params[k].dist = dist + k*nb_item_per_thread;
					params[k].indice = indice + k*nb_item_per_thread;
					if(k==nthreads-1)
						params[k].num_points = actual_num_points_per_chunk - (nthreads-1)*nb_item_per_thread;
					thread_params.push_back(params + k);
				}
				PL.Run(thread_comp_chunk, thread_params);
				for (k = 0; k < nthreads; k++)
					ret.cost += params[k].cost;
            	/*reduce the means and counts*/
            	for (k = 1; k < nthreads; k++){
            		pcount = counts+k*data.num_clusters;
            		pmean = means+k*data.dimension*data.num_clusters;
            		for(j=0;j<data.num_clusters;j++){
            			m = j*data.dimension;
            			for(l=0;l<data.dimension;l++)
            				means[m+l] += pmean[m+l];
            			counts[j] += pcount[j];
            		}
            	}
            	/*save results to nfs*/
            	sprintf(name,"private/chunk-%d-%d.alloc",RANK,i);
            	fp = fopen(name,"wb+");
            	if(!fp) force_exit(("Cannot open "+string(name)+" for writing.").c_str());
            	if(fwrite (membership, sizeof(int), actual_num_points_per_chunk, fp)!=actual_num_points_per_chunk)
            		force_exit(("Cannot write "+string(name)+" successfully.").c_str());
            	fclose(fp);
            	sprintf(name,"private/chunk-%d-%d.smean",RANK,i);
            	fp = fopen(name,"wb+");
            	if(!fp) force_exit(("Cannot open "+string(name)+" for writing.").c_str());
            	if(fwrite (means, sizeof(float), data.dimension*data.num_clusters, fp)!=data.dimension*data.num_clusters)
            		force_exit(("Cannot write "+string(name)+" successfully.").c_str());
            	fclose(fp);
            	sprintf(name,"private/chunk-%d-%d.count",RANK,i);
            	fp = fopen(name,"wb+");
            	if(!fp) force_exit(("Cannot open "+string(name)+" for writing.").c_str());
            	if(fwrite (counts, sizeof(int64), data.num_clusters, fp)!=data.num_clusters)
            		force_exit(("Cannot write "+string(name)+" successfully.").c_str());
            	fclose(fp);
            }
            /*Send message to the master saying that the computation is done.*/
            MPI_Send(&ret, sizeof(KMEANS_SPEC), MPI_CHAR, MASTER_RANK, 0, MPI_COMM_WORLD);
        } else {
            usleep(PROBE_SLEEP); //prevent busy wait.
        }
    }
}


void master_compute_assignment(KMEANS_SPEC& ret, const DATA& data, double size_memory_in_gb){
    static int nthreads = num_thread_per_machine;
    MPI_Status Stat;
    static char name[100];
    static float* centres = 0;
    static uchar* chunk_data = 0;
    static int* membership = 0;
    static float* means = 0;
    static int64* counts = 0;
	static float* sq_cdata = 0;
	static float* sq_cents = 0;
	static float* dist = 0;
	static int* indice = 0;
	static uchar* cent_uchar = 0;
	static int flag_load_chunk_data = true;
	KDTREE<uchar>* kdt = 0;
	if(data.first<0){
    	free(centres);
        free(chunk_data);
        free(membership);
        free(means);
        free(counts);
        free(sq_cdata);
        free(sq_cents);
        if(dist)
            free(dist);
        if(indice)
           free(indice);
        if(kdt)
        	delete kdt;
        if(cent_uchar)
        	free(cent_uchar);
        return;
	}
    /*Begin computation chunk by chunk*/
    int64 max_num_points_per_chunk = \
         (0.8*size_memory_in_gb*(1e9) - (data.dimension*data.num_clusters*sizeof(float)+sizeof(float)*nthreads*data.dimension*data.num_clusters+sizeof(int64)*nthreads*data.num_clusters+ \
		 sizeof(float)*data.num_points+sizeof(float)*data.num_clusters*nthreads+sizeof(float)*data.num_points+sizeof(int)*data.num_points))/(data.dimension*sizeof(uchar));
    int min_num_chunks = ceil((data.last-data.first+1)/(double)max_num_points_per_chunk);
    int64 actual_num_points_per_chunk = 0;
    if(min_num_chunks==1)
        actual_num_points_per_chunk = data.last - data.first + 1;
    else
        actual_num_points_per_chunk = max_num_points_per_chunk;
    ret.num_chunk = min_num_chunks;
    if(min_num_chunks>MAX_NUM_CHUNK)
        force_exit("min_num_chunks>MAX_NUM_CHUNK");
        
    /*load globally shared centres*/
    sprintf(name,"%s/mpikmeans.cents",outpath);
    FILE* fp = fopen(name,"rb");
    if(!fp) force_exit(("Cannot open the file "+string(name)+" for reading.").c_str());
    if(!centres)
        centres = (float*) malloc(data.dimension*data.num_clusters*sizeof(float));
    if(!centres) force_exit("Failed to allocate memory for centres.");
    if (fread(centres, sizeof (float), data.dimension*data.num_clusters, fp) != data.dimension*data.num_clusters)
        force_exit(("The file "+string(name)+" is corrupted.").c_str());
    fclose(fp);
    /*compute each chunk*/
    if(!chunk_data)
        chunk_data = (uchar*) malloc(data.dimension*actual_num_points_per_chunk*sizeof(uchar));
    if(!chunk_data) force_exit("Failed to allocate memory for chunk_data.");
    if(!membership)
        membership = (int*) malloc(sizeof(int)*actual_num_points_per_chunk);
    if(!membership) force_exit("Failed to allocate memory for membership.");
    if(!means)
        means = (float*) malloc(sizeof(float)*nthreads*data.dimension*data.num_clusters);
    if(!means) force_exit("Failed to allocate memory for means.");
    if(!counts)
        counts  = (int64*) malloc(sizeof(int64)*nthreads*data.num_clusters);
    if(!counts) force_exit("Failed to allocate memory for counts.");
	if(!sq_cdata)
		sq_cdata = (float*)malloc(sizeof(float)*data.num_points);
	if(!sq_cdata) force_exit("Failed to allocate memory for sq_cdata.");
	if(!sq_cents)
		sq_cents = (float*)malloc(sizeof(float)*data.num_clusters*nthreads);
	if(!sq_cents) force_exit("Failed to allocate memory for sq_cents.");
	if(!dist && knnsearch==2){
		dist = (float*)malloc(sizeof(float)*data.num_points);
		if(!dist) force_exit("Failed to allocate memory for dist.");
	}
	if(!indice && knnsearch==2){
		indice = (int*)malloc(sizeof(int)*data.num_points);
		if(!indice) force_exit("Failed to allocate memory for indice.");
	}
	if(!cent_uchar && knnsearch==2){
		cent_uchar = (uchar*)malloc(sizeof(uchar)*data.dimension*data.num_clusters);
		if(!cent_uchar) force_exit("Failed to allocate memory for cent_uchar.");
	}
			
	int i,j,k,l,m;
			
	/*build kdtree is necessary*/
	if(knnsearch==2){
		for(i=0;i<data.dimension*data.num_clusters;i++) cent_uchar[i] = centres[i];
		kdt = new KDTREE<uchar>;
		kdt->Build(cent_uchar,data.num_clusters,data.dimension);
	}
	
	
    int64* pcount;
    float* pmean = 0;
    COMP_CHUNK_THREAD_PARAM* params = new COMP_CHUNK_THREAD_PARAM [nthreads];
    for(i = 0;i<min_num_chunks;i++){ //for each chunk.
    	if(min_num_chunks>1 || flag_load_chunk_data){  //reload the chunk data only if min_num_chunks>1 || it has not yet been loaded before.
		    /*load corresponding data points in data.bin*/
		    fp = fopen("user_data/data.bin","rb");
		    if(!fp) force_exit("Failed to open 'user_data/data.bin' for reading.");
		    fseek(fp, (data.first+i*actual_num_points_per_chunk)*data.dimension, SEEK_SET);
		    if(i<min_num_chunks-1){
		        if (fread(chunk_data, sizeof (uchar), data.dimension*actual_num_points_per_chunk, fp) != data.dimension*actual_num_points_per_chunk)
		            force_exit("'user_data/data.bin' is corrupted.");
		    }
		    else{ //the last chunk
		        actual_num_points_per_chunk = (data.last - data.first + 1) % max_num_points_per_chunk;
		        if (fread(chunk_data, sizeof (uchar), data.dimension*actual_num_points_per_chunk, fp) != data.dimension*actual_num_points_per_chunk)
		            force_exit("'user_data/data.bin' is corrupted.");
		    }
		    fclose(fp);
		    flag_load_chunk_data = false;
        }
        ret.first[i] = data.first;
        ret.last [i] = data.first + actual_num_points_per_chunk - 1;
        
        /*compute this chunk in parallel pthread*/
        Parallel PL;
        vector<void*> thread_params;
        int nb_item_per_thread = ceil(actual_num_points_per_chunk/nthreads);
		memset(sq_cdata,0,sizeof(float)*data.num_points);
		memset(sq_cents,0,sizeof(float)*data.num_clusters*nthreads);
		for (k = 0; k < nthreads; k++){
			params[k].num_clusters = data.num_clusters;
			params[k].dimension = data.dimension;
			params[k].membership = membership + k*nb_item_per_thread;
			params[k].means = means + k*data.dimension*data.num_clusters;
			params[k].counts = counts + k*data.num_clusters;
			params[k].chunk_data_part = chunk_data + k*data.dimension*nb_item_per_thread;
			params[k].num_points = nb_item_per_thread;
			params[k].centres = centres;
			params[k].sq_cdata = sq_cdata + k*nb_item_per_thread;
			params[k].sq_cents = sq_cents + k*data.num_clusters;
			params[k].kdt = kdt;
			params[k].dist = dist + k*nb_item_per_thread;
			params[k].indice = indice + k*nb_item_per_thread;
			
			
			if(k==nthreads-1)
				params[k].num_points = actual_num_points_per_chunk - (nthreads-1)*nb_item_per_thread;
			thread_params.push_back(params + k);
		}
        PL.Run(thread_comp_chunk, thread_params);
		ret.cost = 0;
		for (k = 0; k < nthreads; k++)
			ret.cost += params[k].cost;
		
        /*reduce the means and counts*/
        for (k = 1; k < nthreads; k++){
            pcount = counts+k*data.num_clusters;
            pmean = means+k*data.dimension*data.num_clusters;
            for(j=0;j<data.num_clusters;j++){
                m = j*data.dimension;
                for(l=0;l<data.dimension;l++)
                    means[m+l] += pmean[m+l];
                counts[j] += pcount[j];
            }
        }
        
        /*save results to nfs*/
        sprintf(name,"private/chunk-%d-%d.alloc",RANK,i);
        fp = fopen(name,"wb+");
        if(!fp) force_exit(("Cannot open "+string(name)+" for writing.").c_str());
        if(fwrite (membership, sizeof(int), actual_num_points_per_chunk, fp)!=actual_num_points_per_chunk)
            force_exit(("Cannot write "+string(name)+" successfully.").c_str());
        fclose(fp);
        sprintf(name,"private/chunk-%d-%d.smean",RANK,i);
        fp = fopen(name,"wb+");
        if(!fp) force_exit(("Cannot open "+string(name)+" for writing.").c_str());
        if(fwrite (means, sizeof(float), data.dimension*data.num_clusters, fp)!=data.dimension*data.num_clusters)
            force_exit(("Cannot write "+string(name)+" successfully.").c_str());
        fclose(fp);
        sprintf(name,"private/chunk-%d-%d.count",RANK,i);
        fp = fopen(name,"wb+");
        if(!fp) force_exit(("Cannot open "+string(name)+" for writing.").c_str());
        if(fwrite (counts, sizeof(int64), data.num_clusters, fp)!=data.num_clusters)
            force_exit(("Cannot write "+string(name)+" successfully.").c_str());
        fclose(fp);
    }
    delete params;
}

inline bool file_exist (const std::string& name) {
    struct stat buffer;
    return (stat (name.c_str(), &buffer) == 0);
}

int main(int argc, char *argv[]){
    /*MPI initialization.*/
    int num_processors;
    MPI_Init(&argc, &argv);
    MPI_Comm_size(MPI_COMM_WORLD, &num_processors);
    MPI_Comm_rank(MPI_COMM_WORLD, &RANK);
    total_system_memory = (double)getTotalSystemMemory()/1e9;
    if(argc>=3){
		strcpy(outpath,argv[1]);
		sscanf(argv[2],"%d",&knnsearch); // 1 for brute-force search, 2 for kdtree approximate search.
    }
    
    if (RANK == MASTER_RANK){ //master
    	int i,j,k,l,it;
    	FILE* fp = 0;
    	FILE* fp2 = 0;
    	char name [100];
    	char name2 [1000];
    	double J = 0;
    	double Jprev = 0;
    	double ratio;
    	DATA* data = 0;
    	KMEANS_SPEC* results = new KMEANS_SPEC [num_processors];
    	tic();
    	puts("===============================================");
    	printf("Loading Kmeans.conf ...");fflush(stdout);
    	if(argc==3 || argc==4)
    		data = load(RANK);
    	else{
    		puts(usage_msg);
    		force_exit("Parameters are incorrect.");
    	}
    	if(data){
    		printf(" done. Elapsed time is %.4f seconds.\n",toc());
    		printf("    dimension:       %lld\n",data->dimension);
    		printf("    num_points:      %lld\n",data->num_points);
			printf("    num_clusters:    %d\n",data->num_clusters);
			printf("    converge_ratio:  %.6f\n",data->converge_ratio);
			printf("    max_iteration:   %d\n",data->max_iteration);
			printf("    knnsearch:       %d\n",knnsearch);
    	}
    	else
    		force_exit("Error loading data.");
    	if(argc==3){
    		generate_initial_centres(data->num_clusters);  //need to generate initial clusters.
    	}
    	else{ //load initial clusters specified by user.
    		if(file_exist(string(argv[3])))
				system (("cp "+string(argv[3])+" >> user_data/").c_str());
			else{
				printf("[Error] The file %s does not exit.\n",argv[3]);
				return -1;
			}
    	}
    	puts("-----------------------------------------------");
    	float* centres = (float*) malloc(sizeof(float)*data->num_clusters*data->dimension);
    	float* centres_buf = (float*) malloc(sizeof(float)*data->num_clusters*data->dimension);
    	int64* counts = (int64*) malloc(sizeof(int64)*data->num_clusters);
    	int64* counts_buf = (int64*) malloc(sizeof(int64)*data->num_clusters);
    	double avg_time = 0;
    	
    	for(it = 0;it<data->max_iteration;it++){
    		tic();
			/*send DATA struct to all other worker machines.*/
			int cnt = 0;
			data->iter = it;
			int64 nb_points_per_machine = floor(data->num_points/num_processors); 
			for(i = 0;i<num_processors;i++){
				if(i!=MASTER_RANK){
					data->first = cnt*nb_points_per_machine;
					data->last = (cnt+1)*nb_points_per_machine - 1;
					MPI_Send(data, sizeof(DATA), MPI_CHAR, i, 0, MPI_COMM_WORLD);
					cnt++;
				}
			}
			/*compute my own assignment*/
			data->first = cnt*nb_points_per_machine;
			data->last = data->num_points - 1;
			master_compute_assignment(results[RANK], *data, total_system_memory);
			/*wait all results from other worker machines*/
			int dataWaitingFlag;
			cnt = 1;
			MPI_Status Stat;
			while (1) {
		    	/*check whether there is any reply from worker machines*/
		    	MPI_Iprobe(MPI_ANY_SOURCE, MPI_ANY_TAG, MPI_COMM_WORLD, &dataWaitingFlag, &Stat);
		    	if (dataWaitingFlag) { //Receive a packet
		        	/*Extract the packet*/
		        	MPI_Recv(results+Stat.MPI_SOURCE, sizeof(KMEANS_SPEC), MPI_CHAR, Stat.MPI_SOURCE, MPI_ANY_TAG, MPI_COMM_WORLD, &Stat);
		        	cnt++;
		        	if(cnt==num_processors)
		        		break;
		        }
		        else
		        	usleep(PROBE_SLEEP); //prevent busy wait.
		    }
		    
		    /*Calculate performance J, combine count & centres*/
			memset(centres,0,sizeof(float)*data->dimension*data->num_clusters);
			memset(counts,0,sizeof(int64)*data->num_clusters);
			J = 0;
			for(i=0;i<num_processors;i++){ //for each machine.
				for(j=0;j<results[i].num_chunk;j++){  //for each chunk.
				 	sprintf(name,"private/chunk-%d-%d.smean",i,j);
				 	fp2 = fopen(name,"rb");
				    if(!fp2) force_exit(("main: Cannot open "+string(name)+" for reading.").c_str());
				    if (fread(centres_buf, sizeof (float), data->dimension*data->num_clusters, fp2) != data->dimension*data->num_clusters)
				    	force_exit(("main: Cannot read "+string(name)+" successfully.").c_str());	
					fclose(fp2);
				 	sprintf(name,"private/chunk-%d-%d.count",i,j);
				 	fp2 = fopen(name,"rb");
				    if(!fp2) force_exit(("main: Cannot open "+string(name)+" for reading.").c_str());
				    if (fread(counts_buf, sizeof (int64), data->num_clusters, fp2) != data->num_clusters)
				    	force_exit(("main: Cannot read "+string(name)+" successfully.").c_str());	
					fclose(fp2);
					for(k=0;k<data->num_clusters;k++){
						for(l=0;l<data->dimension;l++)
							centres[k*data->dimension+l] += centres_buf[k*data->dimension+l];
						counts[k] += counts_buf[k];
					}
				}
				J += results[i].cost;
			}
			J /= data->num_points;
			if(it==0)
				ratio = 1.0;
			else if (Jprev==0)
				ratio = 0;
			else
				ratio = (Jprev-J)/Jprev;
			if(it>0 && J>=Jprev){  //performance not improved.
				printf("%d\t%.6f\t%.6f\t%.2f [rejected]\n",it+1,J,ratio,toc());
				break;
			}
			/*Normalize each centre and reinitialize if it is empty*/
			for(k=0;k<data->num_clusters;k++){
				if(counts[k]>0){
					for(l=0;l<data->dimension;l++){
						centres[k*data->dimension+l] /= counts[k];
					}
				}
				else{  //randomly pick another sample.
					int64 index = round(((double)rand()/RAND_MAX)*(data->num_points-1));
		        	fp = fopen("user_data/data.bin","rb");
		        	if(!fp) force_exit("main: Failed to open 'user_data/data.bin' for reading.");
		        	fseek(fp, index*data->dimension, SEEK_SET);
		        	fread(centres+k*data->dimension, sizeof (uchar), data->dimension, fp);
		        	counts[k] = 1;
		        	fclose(fp);
				}
			}
			
			/*save mpikmeans.cents, mpikmeans.count*/
		    sprintf(name,"%s/mpikmeans.cents",argv[1]);
		    for(i=0;i<100;i++){
				fp = fopen(name,"wb+");
				if(fp) break;
				else usleep(10000);
		    }
			if(!fp) force_exit(("*main: Cannot open the file "+string(name)+" for writing.").c_str());
		  	if(fwrite (centres, sizeof(float), data->num_clusters*data->dimension, fp)!=data->num_clusters*data->dimension){
		  		force_exit(("main: Cannot write "+string(name)+" successfully.").c_str());
		  	}
			fclose(fp);
		    sprintf(name,"%s/mpikmeans.count",argv[1]);
			fp = fopen(name,"wb+");
			if(!fp) force_exit(("Cannot open the file "+string(name)+" for writing.").c_str());
		  	if(fwrite(counts, sizeof(int64), data->num_clusters, fp)!=data->num_clusters){
		  		force_exit(("main: Cannot write "+string(name)+" successfully.").c_str());
		  	}
			fclose(fp);
			
			/*mpikmeans.alloc*/
			sprintf(name,"%s/mpikmeans.alloc",argv[1]);
			for(i=0;i<num_processors;i++){ //for each machine.
				for(j=0;j<results[i].num_chunk;j++){  //for each chunk.
					sprintf(name2,"private/chunk-%d-%d.alloc",i,j);
					system (("cat "+string(name2)+" >> "+string(name)).c_str());
				}
			}

			/*mpikmeans.desc*/
			sprintf(name,"%s/mpikmeans.desc",argv[1]);
			fp = fopen(name,"w+");
			if(!fp) force_exit(("main: Cannot open the file "+string(name)+" for writing.").c_str());
			fprintf(fp,"converged_cost %.6f\n",J);
			fprintf(fp,"num_iter %d\n",it);
			fprintf(fp,"time_elapsed %.2f",toc());
			fclose(fp);
			
			avg_time += toc();
			
			/*converged?*/
			if(it==data->max_iteration-1 || (it>0 && (Jprev-J)/Jprev<data->converge_ratio)){
				printf("%d\t%.6f\t%.6f\t%.2f [accepted]\n",it+1,J,ratio,toc());
				if(it==data->max_iteration-1) it = data->max_iteration;
				break;
			}else{
				if(it==0) puts("iter\tcost\t\tratio\t\ttime(s)");
				printf("%d\t%.6f\t%.6f\t%.2f\n",it+1,J,ratio,toc());
				Jprev = J;
			}
		}
		/*send convergence signals*/
		data->first = -1; //set termination flag.
		for(i = 0;i<num_processors;i++){
			if(i!=MASTER_RANK){
				MPI_Send(data, sizeof(DATA), MPI_CHAR, i, 0, MPI_COMM_WORLD);
			}
		}
		master_compute_assignment(results[RANK], *data, total_system_memory);
		puts("===============================================");
		/*clean up*/
		free(centres);
		free(centres_buf);
		free(counts);
		free(counts_buf);
		delete results, data;
		if(it==0) it++;
		printf("Average time: %.3f\n",avg_time/it);
    }
    else{
    	
		worker_compute_assignment(total_system_memory);
	}
	
    MPI_Finalize();
}
