from pymodbus.client.sync import ModbusTcpClient
from pymodbus.constants import Endian
from pymodbus.payload import BinaryPayloadDecoder
from mysql.connector.constants import ClientFlag
import mysql.connector
import sys
import time


#data_0 = 122
#formula = "round(data_0/10,2)"
#
#
#xx = eval(formula)
#print(xx)
#quit()

try:
    mydb = mysql.connector.connect(host="localhost", user="root", passwd="root", database="egateway_das")
    mycursor = mydb.cursor()

    print("[V]  EGATEWAY_DAS Database CONNECTED")
except Exception as e:
    print("  [X] EGATEWAY_DAS " + e)

sql = "SELECT analyzer_ip,analyzer_port,unit_id,start_addr,addr_num FROM configurations WHERE id=1"
mycursor.execute(sql)
configuration = mycursor.fetchone()

ipaddress = configuration[0]
portaddress = configuration[1]
startaddr = int(configuration[3])
numaddr = int(configuration[4])
unitid = int(configuration[2])

sql = "TRUNCATE TABLE sensor_values"
mycursor.execute(sql)
mydb.commit()


mycursor.execute("SELECT id,formula FROM sensors ORDER BY id")
sensors = mycursor.fetchall()
for sensor in sensors:    
    sql = "INSERT INTO sensor_values (sensor_id,data) VALUES ('" + str(sensor[0]) + "','0')"
    mycursor.execute(sql)
    mydb.commit()

data = [0 for i in range(100)] 
while(True):
    try:
        client = ModbusTcpClient(ipaddress,port=portaddress)
        client.connect()
        result = client.read_holding_registers(startaddr,numaddr,unit=unitid)
        client.close()
        
        xx = startaddr
        yy = 0
        while(yy < numaddr):
            data[yy] = result.registers[yy]
            xx += 1
            yy += 1
            
        #print(result.registers)
        client = ModbusTcpClient(ipaddress,port=portaddress)
        client.connect()
        result = client.read_input_registers(5000,1,unit=unitid)
        client.close()
        data[21] = result.registers[0]
        
        mycursor.execute("SELECT id,formula FROM sensors ORDER BY id")
        sensors = mycursor.fetchall()
        for sensor in sensors:
            sql = "UPDATE sensor_values SET data = '" + str(eval(sensor[1])) + "' WHERE sensor_id= '" + str(sensor[0]) + "';"
            mycursor.execute(sql)
            mydb.commit()
        
        time.sleep(1)
    except Exception as e:
        print("  [X] Modbus Not Connected! ")
        print(e)
        time.sleep(10)