

import sklearn
from sklearn import datasets
from sklearn import svm

#f = open("testingData.txt","r+")
#string  = f.readline()
#print string

#get the class labels from training set
target =[];

with open("trainingData.txt","r+") as f:
		for line in f:
			 target.append(int(line[0]))

#print target


#populate the samples in a [m_samples n_features] array foramt
counter =0
samples =[ [] for i in range(len(target)) ]

with open("trainingData.txt","r+") as f:
	for lines in f:
		line = lines.split()
		for values in line[1:]:
				samples[counter].append(int(values))

		counter=counter+1
			
testing_features =[ [] for i in range(len(target)) ]

counter = 0
with open("testingData.txt","r+") as f:
	for lines in f:
		line = lines.split()
		for values in line[1:]:
				testing_features[counter].append(int(values))

		counter=counter+1
			
print "samples length" 
print len(samples)
print "feature size" 
print len(samples[0])

print "target length"
print len(target)


boston = datasets.load_boston()
X, y = boston.data[:-1] , boston.target[:-1]

clf = svm.SVC(probability=True)
clf.fit(samples,target)
probs =clf.predict_proba(testing_features)
print probs
ans = clf.predict(testing_features)
print ans

file = open("result.txt","r+")
file.writelines(["%s\n" % item  for item in ans])

file = open("result_probs.txt","r+")
file.writelines(["%s\n" % item  for item in probs])
