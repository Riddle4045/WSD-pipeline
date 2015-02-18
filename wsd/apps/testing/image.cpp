#include"stdio.h"
#include<string>
#include <math.h>
#include<memory.h>

#define DIM_FEAT 128

using namespace std;

int main(int argc, char*argv[]){
	/*root_user + full_path_of_test_bov_feat*/
	if(argc!=3)
		return -1;
	string test = argv[2];
	FILE* fp = fopen((string(argv[1])+"/kmeans/out/mpikmeans.cents").c_str(),"rb");
            if(!fp){
                printf("Failed to open %s\n", (string(argv[1])+"/kmeans/out/mpikmeans.cents").c_str());
                return -1;
            }
     fseek(fp, 0L, SEEK_END);
     int num_centres = ftell(fp)/(4*DIM_FEAT);
    fseek(fp, 0L, SEEK_SET);
    fclose(fp);
	//load testing feat.
	int* tf = new int [num_centres];
	fp = fopen(test.c_str(),"rb");
	if(!fp){
		printf("Cannot open: *%s*\n",test.c_str());
		return -1;
	}
	fread(tf, sizeof(int), num_centres, fp);
	fclose(fp);
	//load gallery samples.
	fp = fopen((argv[1]+string("/labeled_train_feat.int32")).c_str(),"rb");
	if(!fp){
		printf("Cannot open: *%s*\n",(argv[1]+string("/labeled_train_feat.int32")).c_str());
		return -1;
	}//interpretation of a dream in which you saw.feat
    fseek(fp, 0L, SEEK_END);
    int num_samples = ftell(fp)/(4*(1+num_centres));
    fseek(fp, 0L, SEEK_SET);
    int* train = new int [(1+num_centres)*num_samples];
    fread(train, sizeof(int), (1+num_centres)*num_samples, fp);
	fclose(fp);
	float* score = new float [num_samples];
	memset(score,0,sizeof(float)*num_samples);
	double l1, l2 = 0, c;
	for(int j = 0;j<num_centres;j++)
		l2 += tf[j]*tf[j];
	l2 = sqrt(l2);
	int num_class = 0;
	for(int i = 0;i<num_samples;i++){
		l1 = 0, c = 0;
		for(int j = 0;j<num_centres;j++){
			l1 += train[i*(1+num_centres)+j]*train[i*(1+num_centres)+j];
			c += train[i*(1+num_centres)+j] * tf[j];
		}
		l1 = sqrt(l1);
		score[i] = c /(l1*l2);
		if(num_class<train[i*(1+num_centres)+num_centres])
			num_class = train[i*(1+num_centres)+num_centres];
		//printf("%d  %.4f\n",train[i*(1+num_centres)+num_centres],score[i]);
	}
	num_class++;
	float* avg_score = new float [num_class];
	memset(avg_score,0,sizeof(float)*num_class);
	int* count = new int [num_class];
	memset(count,0,sizeof(int)*num_class);
	for(int i = 0;i<num_samples;i++){
		avg_score[train[i*(1+num_centres)+num_centres]] += score[i];
		count[train[i*(1+num_centres)+num_centres]]++;
	}
	double sum = 0;
	for(int i = 0;i<num_class;i++){
		avg_score[i] /= count[i];
		sum += avg_score[i];
	}
	for(int i = 0;i<num_class;i++){
		avg_score[i] /= sum;
		if(i==num_class-1)
			printf("%.4f",avg_score[i]);
		else
			printf("%.4f ",avg_score[i]);
	}
}




