#include "main.h"
#include <dirent.h>
#include <sys/stat.h>
#include <flann/flann.hpp>


#define DIM_FEAT 128
using namespace std;

//this function lists files under a folder.
vector<string>ls(string path){
    DIR *dpdf;
    struct dirent *epdf;
    dpdf = opendir(path.c_str());
    vector<string> ret;
    if (dpdf != NULL){
        while (epdf = readdir(dpdf)){
        	string name = epdf->d_name;
        	if(name != "." && name != ".." && name.find(".sift")!=string::npos){
        		ret.push_back(path+"/"+epdf->d_name);
        	}
        }
    }
    return ret;
}

template<class T>
        class KDTREE{
    flann::SearchParams sp;
    flann::KDTreeIndexParams ip;
    flann::Index<flann::L2<T> >* index;
    int nrow, ncol; //size of the dataset based on which the KD tree was built.
        public:
            inline KDTREE():ip(5){ //the number of randomized trees.
                index = NULL;
                /*setting SearchParams*/
                sp.checks = 150;  //specifies the maximum leafs to visit when searching for neighbours
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
            
            /*data is nrow x ncol matrix*/
            inline void Build(T* data, int nrow, int ncol){
                this->nrow = nrow;
                this->ncol = ncol;
                flann::Matrix<T> dataset (data, nrow, ncol);
                if(index) free(index);
                index = new flann::Index< flann::L2<T> >(dataset, ip);
                index->buildIndex();
            }
            
            /* SEARCH: Perform nearest neighbor search.
             * keys[In]: nb_key x ncol matrix, each row containing one sample to be queried.
             * nb_key[In]: the number of keys to be queried.
             * dist[Out]: nb_key x 1 matrix, with each element represents the distance to nearest neighbor.
             * indice[Out]: nb_key x 1 matrix, with each element represents the index of the nearest neighbor in the dataset based on which KD tree was built.
             */
            inline void Search(T* keys, const int& nb_key, float* dist, int* indice){
                flann::Matrix<T> queries (keys, nb_key, this->ncol);
                flann::Matrix<int> indices(indice, nb_key, 1);
                flann::Matrix<float> dists(dist, nb_key, 1);
                index->knnSearch(queries, indices, dists, 1, sp);
            }
};

inline bool exists (const std::string& name) {
  struct stat buffer;   
  return (stat (name.c_str(), &buffer) == 0); 
}

string SplitFilename (const std::string& str)
{
  std::cout << "Splitting: " << str << '\n';
  unsigned found = str.find_last_of("/\\");
  return str.substr(found+1);
}


//bin/proc ../../users/192.168.1.201 ../../download/tank fish
int main(int argc, char* argv[]){
	if(argc !=6){
		puts("Five inputs are required: path_to_user_root + path_to_word_root + sense + index_of_sense + test_sample_path");  //index_of_sense begins at 0.
		return -1;
	}
    /*Load K-means centres & build encoding KD tree.*/
    float* centres_buf = 0;
    int num_centres = 0;
    KDTREE<float>* kdt = 0;
        {
            {
            FILE* fp = fopen((string(argv[1])+"/kmeans/out/mpikmeans.cents").c_str(),"rb");
            if(!fp){
                printf("Failed to open %s\n", (string(argv[1])+"/kmeans/out/mpikmeans.cents").c_str());
                return -1;
            }
            fseek(fp, 0L, SEEK_END);
            num_centres = ftell(fp)/(4*DIM_FEAT);
            fseek(fp, 0L, SEEK_SET);
            centres_buf = new float [num_centres*DIM_FEAT];
            int k = fread(centres_buf, sizeof(float), num_centres*DIM_FEAT, fp);
            if(k !=  num_centres*DIM_FEAT){
                printf("Cannot read centre file successfully. Expected to read %d elements, but %d were read. path = %s\n",num_centres*DIM_FEAT,k,(string(argv[1])+"/kmeans/out/mpikmeans.cents").c_str());
                return -1;
            }
            fclose(fp);
            kdt = new KDTREE<float>;
            kdt->Build(centres_buf,num_centres,DIM_FEAT);
            printf("Number of centres is: %d\n",num_centres);
            }
        }
	vector<string> train = ls(argv[2]+string("/train/")+argv[3]);
	vector<string> test = ls(argv[5]);
	
	float* buf_float = new float [5000*DIM_FEAT];
	unsigned char* buf_uchar = new unsigned char [5000*DIM_FEAT];
	float* dist = new float [5000];
	int* indice = new int [5000];
	int* hist = new int [(1+num_centres)*train.size()];
	int* ph;
	int label;
	sscanf(argv[4],"%d",&label);
	for(int i = 0;i<train.size();i++){
        FILE* fp = fopen(train[i].c_str(),"rb");
        if(fp){
        	int k = fread(buf_uchar, sizeof(unsigned char), 5000*DIM_FEAT, fp);
        	for(int j = 0;j<k;j++){
            	buf_float[j] = buf_uchar[j];
        	}
        	kdt->Search(buf_float, k/DIM_FEAT, dist, indice);
        	ph = hist+i*(1+num_centres);
        	memset(ph,0,sizeof(int)*(1+num_centres));
        	for(int j = 0;j<k/DIM_FEAT;j++)
        		ph[indice[j]]++;
        	ph[num_centres] = label; //the last unit is for label.
    	}
    	fclose(fp);
	}
	
    FILE* fpout = fopen((string(argv[1])+"/labeled_train_feat.int32").c_str(),"a+b");  //write binary appended.
    if(fpout)
    	fwrite (hist, sizeof(int), (1+num_centres)*train.size(), fpout);
    fclose(fpout);
	
	
	string outpath = string(argv[1])+"/test_image_feat/";
	for(int i = 0;i<test.size();i++){
		string out_file = outpath + SplitFilename(test[i].substr(0,test[i].size()-5)+".feat");
		if(exists (out_file.c_str()))
			continue;
        FILE* fp = fopen(test[i].c_str(),"rb");
        if(fp){
        	int k = fread(buf_uchar, sizeof(unsigned char), 5000*DIM_FEAT, fp);
        	for(int j = 0;j<k;j++){
            	buf_float[j] = buf_uchar[j];
        	}
        	kdt->Search(buf_float, k/DIM_FEAT, dist, indice);
        	memset(hist,0,sizeof(int)*num_centres);
        	for(int j = 0;j<k/DIM_FEAT;j++)
        		hist[indice[j]]++;
        	FILE* fpout = fopen(out_file.c_str(),"wb+");
        	if(fpout)
        		fwrite (hist, sizeof(int), num_centres, fpout);
        	fclose(fpout);
    	}
    	fclose(fp);
	}
	delete centres_buf, buf_float, buf_uchar, dist, indice, hist;
}



