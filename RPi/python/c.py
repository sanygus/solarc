#!/usr/bin/python

import smbus,sys,subprocess,time

while(True):
	try:
		smbus.SMBus(1).write_byte(0x04,1)
		file = open("/tmp/osv","w")
		file.write(str((smbus.SMBus(1).read_byte(0x04)*4-220)/7.76))
		file.close()

		smbus.SMBus(1).write_byte(0x04,9)
		vlt = smbus.SMBus(1).read_byte(0x04)*4*0.0049
		file = open("/tmp/vlt","w")
		file.write(str(vlt))
		file.close()

		file = open("/home/pi/python/voff.var","rt")
		voff = float(file.read())
		file.close()
		file = open("/home/pi/python/von.var","rt")
		von = int(float(file.read())/0.0049/4)
		file.close()
		if von>255:
			von=255
		smbus.SMBus(1).write_byte(0x04,10)
		smbus.SMBus(1).write_byte(0x04,von)
		
		smbus.SMBus(1).write_byte(0x04,3)

		s = subprocess.check_output("ping 8.8.8.8 -c 1 -W 1;exit 0",stderr=subprocess.STDOUT,shell=True)
		if s.find("icmp_req=1 ttl=") > 0:
			smbus.SMBus(1).write_byte(0x04,4)
		else:
			smbus.SMBus(1).write_byte(0x04,5)

		s = subprocess.check_output("ping 10.16.1.1 -c 1 -W 1;exit 0",stderr=subprocess.STDOUT,shell=True)
		if s.find("icmp_req=1 ttl=") > 0:
			smbus.SMBus(1).write_byte(0x04,6)
		else:
			smbus.SMBus(1).write_byte(0x04,7)

		smbus.SMBus(1).write_byte(0x04,2)
		if smbus.SMBus(1).read_byte(0x04)==2 or vlt<voff:
			smbus.SMBus(1).write_byte(0x04,8)
			subprocess.check_output("shutdown -h now",shell=True)
	
	except IOError:
		print("IOError")

	time.sleep(3)

