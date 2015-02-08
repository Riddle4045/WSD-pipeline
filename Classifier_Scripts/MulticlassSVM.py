

import sklearn
from sklearn import datasets
from sklearn import svm

#f = open("testingData.txt","r+")
#string  = f.readline()
#print string

#get the class labels from training set
target =[];

with open("testingData.txt","r+") as f:
		for line in f:
			 target.append(int(line[0]))

#print target


#populate the samples in a [m_samples n_features] array foramt
counter =0
samples =[ [] for i in range(len(target)) ]

with open("testingData.txt","r+") as f:
	for lines in f:
		line = lines.split()
		for values in line[1:]:
				samples[counter].append(int(values))

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
clf.fit(samples[:-1],target[:-1])
probs =clf.predict_proba(samples[-1])
print probs
ans = clf.predict(samples[-1])
print ans


