import sklearn
from sklearn import datasets
from sklearn import svm
import sys

#f = open("testingData.txt","r+")
#string  = f.readline()
#print string

folder = "/home/yp/projects/data/uiuc/"
if len(sys.argv) >= 2:
	folder += sys.argv[1]
else:
	folder += "bass"

train = folder + "/img/training.txt"
test = folder + "/img/testing.txt"
results = folder + "/img/results.txt"

#get the class labels from training set
target =[];
#populate the samples in a [m_samples n_features] array foramt
samples =[]
with open(train,"r+") as f:
	for line in f:
		target.append(int(line[0]))
		ls = line.split("\t")[1].split()
		feature = []
		for value in ls:
			feature.append(int(value))
		samples.append(feature)
#print target
#reading testing features			
testing_features = []
testing_truth  = []
with open(test,"r+") as f:
	for line in f:
		testing_truth.append(int(line.split("\t")[0]))
		ls = line.split("\t")[1].split()
		feature = []
		for value in ls:
			feature.append(int(value))
		testing_features.append(feature)
			
print "samples length " + str(len(samples))
print "feature size" + str(len(samples[0]))
print "target length " + str(len(target))
print "testing_features length " + str(len(testing_features))
print "testing_truth length " + str(len(testing_truth))


boston = datasets.load_boston()
X, y = boston.data[:-1] , boston.target[:-1]

clf = svm.SVC(probability=True)
clf.fit(samples,target)
probs =clf.predict_proba(testing_features)
print "probs length " + str(len(probs))
ans = clf.predict(testing_features)
print "ans length " + str(len(ans))

f = open(results,"w")
for i in range(len(testing_features)):
	f.write(str(ans[i]) + " ")
	f.write(str(probs[i][0]) + " ")
	f.write(str(probs[i][1]) + " ")
	f.write(str(testing_truth[i]) + "\n")
f.close()
print "SVM training and testing is done"
	

