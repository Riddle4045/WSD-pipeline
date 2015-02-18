from multiprocessing import Pool
import time
import subprocess
import sys

def download(dic): #word,sense,start
	proc = subprocess.Popen("php "+dic['script']+" "+dic['word']+" "+dic['sense']+" "+str(dic['start']), shell=True, stdout=subprocess.PIPE)
	ret = proc.stdout.read()
	return ret

if __name__ == '__main__':  # python download.py word s1 s2 ...
	ns = len(sys.argv) - 2
	if(ns<1):
		print "Usage: python download.py word s1 s2 ..."
	else:
		args = []
		for s in range(0,ns):
			for start in range(8,108,20): #train
				args.append({'script':'download_train.php','word': sys.argv[1], 'sense': sys.argv[s+2], 'start':start})
			for start in range(0,8,4): #test
				args.append({'script':'download_test.php','word': sys.argv[1], 'sense': sys.argv[s+2], 'start':start})
		p = Pool(processes=len(args))
		print(p.map(download, args))
