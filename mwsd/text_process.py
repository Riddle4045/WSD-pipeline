import os
folder = "/home/yp/projects/data/uiuc/bass"
dirPath = folder + "/img/test";
f = open(folder + "/txt/test.txt","w");
for dirName, subdirList, fileList in os.walk(dirPath):
			for file in fileList:
							file = file.replace("_"," ");
							f.write(file[2:-4] + "\n");

f.close()
