from pymodbus.client.sync import ModbusTcpClient
from pymodbus.constants import Endian
from pymodbus.payload import BinaryPayloadDecoder
import sys
import time

ipaddress = "192.168.143.211"
portaddress = "502"
startaddr = 2000
numaddr = 51
unitid = 1
 
while(True):
    try:
        client = ModbusTcpClient(ipaddress,port=portaddress)
        client.connect()
        result = client.read_holding_registers(startaddr,numaddr,unit=unitid)
        client.close()
        print(result.registers)
        
        time.sleep(1)
    except Exception as e:
        print("  [X] Modbus Not Connected! ")
        print(e)
        time.sleep(10)