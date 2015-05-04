#get images
#extract features
#save file
#init_k-Means
#createVisualVocav
from sklearn.cluster import KMeans
from sklearn import datasets
from sklearn.cluster import MiniBatchKMeans
import cv2
import numpy as np
import os



#baseimages = "/home/hduser/Documents/WSD-evaluation data/UIUCsampled/bass/BaseImages";
baseimages = "/home/hduser/Documents/tanker";
trainimages = "/home/hduser/Documents/train";
testimages = "/home/hduser/Documents/test";
trainingData = "trainingData.txt";
testingData = "testingData.txt";
features = []
train_features = []
n_clusters = 200;
k_means=  MiniBatchKMeans(n_clusters=n_clusters,max_iter=300,batch_size=500,verbose=1);

def getSiftfeatures(baseimages,features):	
	#walk  through the directory and write in outputfile
	totalfeatures = 0;
	for subdir, dirs, files in os.walk(baseimages):
		for file in files:
					fileName = os.path.join(subdir,file);
					img = cv2.imread(fileName);
					gray= cv2.cvtColor(img,cv2.COLOR_BGR2GRAY)
					sift = cv2.SIFT()
					kp, des = sift.detectAndCompute(gray,None);
					print "doing : " + file 
					totalfeatures = totalfeatures + len(des);
					for (x,y),value in np.ndenumerate(des):
									temp =  des[x];
									features.append(temp);
					
	return features,totalfeatures;


def makeVisualWords(features,k_means):
				print len(features) 
				print len(features[0])
				k_means.fit(features);
				labels = k_means.labels_;
				print labels;
				print k_means.get_params();



def quantinzeimages(trainimages,k_means,trainingData,n_clusters):
		for subdir, dirs, files in os.walk(baseimages):
					for file in files:
								img_features = [];	
								fileName = os.path.join(subdir,file);
								img = cv2.imread(fileName);
								gray= cv2.cvtColor(img,cv2.COLOR_BGR2GRAY)
								sift = cv2.SIFT()
								kp, des = sift.detectAndCompute(gray,None);
								for (x,y),value in np.ndenumerate(des):
											 temp =  des[x];
											 img_features.append(temp);
								p =  k_means.predict(img_features);
								writeHistogramtoFile(p,file,trainingData,n_clusters);
			
def writeHistogramtoFile(p,file,trainingData,n_clusters):
			hist = [ 0 for i in range(0,n_clusters) ];
			print p
			for index in p:
		#				print index
						hist[index] = hist[index] + 1;
			for i in range(len(hist)):
					    hist[i] = hist[i]/128;
			f = open(trainingData,"a");
			print hist
			feature = ' '.join(str(e) for e in hist);
			feature = feature + "\n"
			f.write(feature)
			f.close();
	
features,a = getSiftfeatures(baseimages,features);
makeVisualWords(features,k_means);
print "And we have words!"
quantinzeimages(trainimages,k_means,trainingData,n_clusters);
print "train images quantized\n\n" 
quantinzeimages(testimages,k_means,testingData,n_clusters);
print "testing images quantized\n\n"

