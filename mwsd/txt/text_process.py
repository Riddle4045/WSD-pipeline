import os
import sys
folder = "/home/yp/projects/data/uiuc/"
if len(sys.argv) >= 2:
	folder += sys.argv[1]
else:
	folder += 'bass'
dirPath = folder + "/img/test";
f = open(folder + "/txt/test.txt","w");
for dirName, subdirList, fileList in os.walk(dirPath):
	for p in fileList:
		#print p
		p = p.replace("_"," ");
		lable = int(p[0])
		f.write(str(lable) + "\t")
		f.write(p[2:-4] + "\n");
f.close()
