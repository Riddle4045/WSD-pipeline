#Usage
#	sense_dist.py word
#Author: Dihong
import sys, getopt, os, os.path
import nltk
from nltk.corpus import wordnet as wn
import subprocess
from decimal import Decimal
from numpy import zeros
import time as dt

def main(argv):
	word = argv[1]
	seed = argv[2]
	train = argv[3]
	test = argv[4]
	folder = argv[5]
	classifier = folder + "/" + word + ".classifier"
	results = folder + "/" + word + ".disambiguated"
	#get definition of word from WordNet.
	print 'Word to be disambiguated: %s. The senses in the WordNet are:' % word
	if os.path.isfile(seed) == False or os.path.isfile(train) == False or os.path.isfile(test) == False:
		print 'Error: the input file(s) does not exist.'
		sys.exit(2)
	#Train classifier
	n1=dt.time()
	sys.stdout.write('Training classifier ... ')
	cmd = './uwsd -train '+ train + ' '+ seed +' '+ classifier+' ' + word
	os.system(cmd)
	n2=dt.time()
	print 'done. Elapsed time is %.4f seconds.' % ((n2-n1))
	n1=dt.time()
	#Classify the text
	sys.stdout.write('Classifying text ... ')
	cmd = './uwsd -test '+ classifier +' '+ test +' ' + word
	out = subprocess.check_output(cmd, shell=True)
	fd = open(results, 'w');
	fd.truncate()
	fd.write(out)
	fd.close()
	n2=dt.time()
	print 'done. Elapsed time is %.4f seconds.' % ((n2-n1))

				
				
if __name__ == "__main__":
	if len(sys.argv)  != 6:
		print 'Not enough arguments.';
		sys.exit(2);
	main(sys.argv)
