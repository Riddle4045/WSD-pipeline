# to generate outputs with the same format as image classification
import os
import sys

def output(word, folder):
	truth = folder + "/" + word + ".test"
	res = folder + "/" + word + ".disambiguated"
	out = folder + "/" + word + ".txt"
	print truth, res, out		
	target = []
	t = open(truth, "r+")
	for line in t:
		#print line
		target.append(int(line[0]))
	print len(target)
	prob = []
	labels = []
	l = open(res, "r+")
	for line in l:
		lab = int(line.split()[0])
		pro = float(line.split()[1])
		if lab == 0 or lab == -1:
			labels.append(0)
		else:
			labels.append(lab)
		if lab == 0 or lab == - 1:
			p = []
			p.append(0.0)
			p.append(0.0)
			prob.append(p)
		elif lab == 1:
			p = []
			p.append(pro)
			p.append(1.0 - pro)
			prob.append(p)
		else:
			p = []
			p.append(1.0 - pro)
			p.append(pro)
			prob.append(p)
	print len(labels)
	print len(prob)
	# write the results
	o = open(out, "w")
	for i in range(len(target)):
		o.write(str(labels[i]) + " ")
		o.write(str(prob[i][0]) + " ")
		o.write(str(prob[i][1]) + " ")
		o.write(str(target[i]) + "\n")
	o.close()	

output(sys.argv[1], sys.argv[2])
	
