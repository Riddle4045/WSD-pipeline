import os
folder = "/home/yp/projects/data/uiuc/squash"
dirPath = folder + "/img/test";
f = open(folder + "/txt/test.txt","w");
for dirName, subdirList, fileList in os.walk(dirPath):
	for p in fileList:
		p = p.replace("_"," ");
		f.write(p[2:-4] + "\n");
f.close()
