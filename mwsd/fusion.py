import os
import sys


def readResults(filename):
    	ls = []
	ps = []
	ts = []
    	f = open(filename,"r+")
        for line in f:
        	es = line.split();
	       	ls.append(int(es[0]))
		p = []
		p.append(float(es[1]))
		p.append(float(es[2]))
		ps.append(p)
		ts.append(int(es[3]))
	f.close()
	return ls, ps, ts
	
def getCMatrix(ls, ts):
	m00, m01, m10, m11 = 0, 0, 0, 0
	for i in range(len(ls)):
		if ts[i] == 1:
			if ls[i] == ts[i]:
				m00 += 1
			else:
				m01 += 1
		else:
			if ls[i] == ts[i]:
				m11 += 1
			else:
				m10 += 1
	print m00, m01, m10, m11
	return m00, m01, m10, m11

def fusion(il, ip, it, tl, tp, tt):
	fl, fp, ft = [], [], tt
	for i in range(len(il)):
		p1 = ip[i][il[i] - 1]
		p2 = 0.0
		if tl[i] != 0:
			p2 = tp[i][tl[i] - 1]
		if p1 > p2:
			fl.append(il[i])
			fp.append(ip[i])
		else:
			fl.append(tl[i])
			fp.append(tp[i])
	return fl, fp, ft

il, ip, it = readResults(sys.argv[1])
print "image"
getCMatrix(il, it)
tl, tp, tt = readResults(sys.argv[2])
print "text"
getCMatrix(tl, tt)
fl, fp, ft = fusion(il, ip, it, tl, tp, tt)
print "fusion"
getCMatrix(fl, ft)
