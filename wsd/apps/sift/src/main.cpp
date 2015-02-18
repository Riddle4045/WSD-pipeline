#include "main.h"
#include<opencv2/highgui/highgui.hpp>
#include<opencv/cv.h>
#include <dirent.h>
#include <sys/stat.h>


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
        	if(name != "." && name != "..")
        		ret.push_back(path+"/"+epdf->d_name);
        }
    }
    return ret;
}

inline bool exists (const std::string& name) {
  struct stat buffer;   
  return (stat (name.c_str(), &buffer) == 0); 
}

//bin/proc ../../download/tank fish
int main(int argc, char* argv[]){
	if(argc !=4){
		puts("Three inputs are required: path_to_word_root + sense + test_sample_path");
		return -1;
	}
	DESC* sift = new DESC (0);
	vector<string> train = ls(argv[1]+string("/train/")+argv[2]);
	vector<string> test = ls(argv[3]);  //list all testing samples in the testing path.
	float* features = new float [DIM_FEAT*10000];
	unsigned char* uf = new unsigned char [DIM_FEAT*10000];
	for(int i = 0;i<train.size();i++){
		IplImage* im_gray  = cvLoadImage(train[i].c_str(), CV_LOAD_IMAGE_GRAYSCALE);
		if(im_gray==0 || exists(train[i]+".sift")) continue;
		
        int nkeypnts = sift->extract_feat((unsigned char*)im_gray->imageData, im_gray->widthStep, im_gray->height, im_gray->width, features, 10000);
		for(int i=0;i<DIM_FEAT*nkeypnts;i++)
			uf[i] = round(255*features[i]);
		cvReleaseImage(&im_gray);
		FILE* fp = fopen((train[i]+".sift").c_str(),"wb+");
		printf("image name = %s, nkeypnts = %d\n",train[i].c_str(),nkeypnts);
		if(fp){
			fwrite (uf , sizeof(unsigned char), DIM_FEAT*nkeypnts, fp);
			fclose (fp);
		}
	}
	for(int i = 0;i<test.size();i++){
		IplImage* im_gray  = cvLoadImage(test[i].c_str(), CV_LOAD_IMAGE_GRAYSCALE);
		if(im_gray==0){
			printf("warning: cannot load image: %s\n",test[i].c_str());
		}
		if(im_gray==0 || exists(test[i]+".sift")) continue;
		
        int nkeypnts = sift->extract_feat((unsigned char*)im_gray->imageData, im_gray->widthStep, im_gray->height, im_gray->width, features, 10000);
		for(int i=0;i<DIM_FEAT*nkeypnts;i++)
			uf[i] = round(255*features[i]);
		if(nkeypnts<10)
			printf("warning: %s has only %d keypoints.\n",test[i].c_str(),nkeypnts);
		cvReleaseImage(&im_gray);
		FILE* fp = fopen((test[i]+".sift").c_str(),"wb+");
		printf("image name = %s, nkeypnts = %d\n",test[i].c_str(),nkeypnts);
		if(fp){
			fwrite (uf , sizeof(unsigned char), DIM_FEAT*nkeypnts, fp);
			fclose (fp);
		}
	}
	delete features, uf;
}



