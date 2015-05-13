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


folder = "/home/yp/projects/data/uiuc/bass/img";
#baseimages = "/home/hduser/Documents/WSD-evaluation data/UIUCsampled/bass/BaseImages";
baseimages = folder + "/base";
basefeatfile = folder + "/basefeat.txt"
trainimages = folder + "/train";
testimages = folder + "/test";
trainingData = folder + "/training.txt";
testingData = folder + "/testing.txt";
features = []
train_features = []
n_clusters = 200
k_means=  MiniBatchKMeans(n_clusters=n_clusters,max_iter=100,batch_size=500,verbose=1)

def getSiftfeatures(baseimages,basefeatfile):
	print "feature extraction begins"	
	#walk  through the directory and write in outputfile
	totalfeatures = 0;
	x = 0
	features = []
	#print baseimages
	f = open(basefeatfile, 'w')
	# two ways of storing the features, one in memory, one in disk
	for root, dirs, files in os.walk(baseimages):
		for file in files:
			filename = os.path.join(root,file);
			#print filename
			img = cv2.imread(filename);
			gray= cv2.cvtColor(img,cv2.COLOR_BGR2GRAY)
			#sift = cv2.SIFT()
			sift = cv2.xfeatures2d.SIFT_create() 
			kp, des = sift.detectAndCompute(gray,None);
			print str(x) + " feature extraction : " +  str(len(des)) + " " + file 
			x += 1
			totalfeatures += len(des);
			for y in des:
				feature = []
				for z in y:
					feature.append(z)
					f.write(str(z))
					f.write(' ') 
				f.write('\n')
				features.append(feature)
	print "feature extraction is done: " + str(totalfeatures)
	return features, totalfeatures;


def makeVisualWords(features,k_means):
	print "k-means begins"
	print len(features) 
	print len(features[0])
	k_means.fit(features);
	labels = k_means.labels_;
	print labels;
	print k_means.get_params();
	print "k-means ends"



def quantinzeimages(images,k_means,data,n_clusters):
	print "k-means "
	f = open(data,"w");
	for root, dirs, files in os.walk(images):
		for file in files:
			img_features = []	
			filename = os.path.join(root,file);
			print filename
			img = cv2.imread(filename);
			gray= cv2.cvtColor(img,cv2.COLOR_BGR2GRAY)
			sift = cv2.xfeatures2d.SIFT_create()
			kp, des = sift.detectAndCompute(gray,None);
			for y in des:
				feature = []
				for z in y:
					feature.append(z)
				img_features.append(feature)
			p =  k_means.predict(img_features);
			writeHistogramtoFile(p,f,data,n_clusters);
	f.close();	
		
def writeHistogramtoFile(p,f,data,n_clusters):
	hist = [ 0 for i in range(n_clusters) ];
	print p
	for index in p:
		#print index
		hist[index] += 1;
	#for i in range(len(hist)):
	#	hist[i] = hist[i]/128;
	print hist
	feature = ' '.join(str(e) for e in hist);
	feature = feature + "\n"
	f.write(feature)
	#f.close();

def readFeatures(featfile):
	print "reading features from files"
	f = open(featfile, 'r')
	x = 0
	features = []
	for line in f:
		ns = line.split()
		feature = []
		for i in xrange(0, len(ns)):
			feature.append(float(ns[i]))
		features.append(feature)
		if x != 0 and x % 10000 == 0:
			print 'line ' + str(x) + ' is processed'
		x += 1
	print "file reading is complete"
	return features
	
#features, a = getSiftfeatures(baseimages,basefeatfile);
features = readFeatures(basefeatfile)
makeVisualWords(features,k_means)
print "And we have words!"
quantinzeimages(trainimages,k_means,trainingData,n_clusters);
print "train images quantized\n\n" 
quantinzeimages(testimages,k_means,testingData,n_clusters);
print "testing images quantized\n\n"

