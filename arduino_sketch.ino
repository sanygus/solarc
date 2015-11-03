#include <Wire.h>

byte act=0;//1-get_osv,2-monit_button,3-sig_on
byte res=0;//out
byte sigont=0;
byte von=50;//0-255//~1v
bool softoff=false;
int softofft=0;
bool readdata=false;

void setup() {
  pinMode(7,OUTPUT);
  pinMode(8,INPUT);
  pinMode(10,OUTPUT);
  pinMode(11,OUTPUT);
  pinMode(12,OUTPUT);
  
  digitalWrite(7,HIGH);
  
  Wire.begin(0x04);
  Wire.onReceive(rd);//получение
  Wire.onRequest(sd);//отправка
  Serial.begin(9600);
  Serial.println("start");
}

void loop() {
  if((sigont>=1)&&(sigont<=6)){digitalWrite(10,HIGH);sigont++;}
  else{digitalWrite(10,LOW);digitalWrite(11,LOW); digitalWrite(12,LOW);sigont=0;};
  
  if(softoff&&((analogRead(1)/4)>=von)){digitalWrite(7,HIGH);softoff=false;Serial.println("softoff=false");};
  if(softofft==20){softofft=0;digitalWrite(7,LOW);softoff=true;Serial.println("softoff=true");};
  if(softofft>=1){softofft++;Serial.println(softofft);};
  
  delay(1000);
}

void rd(int cb){
  if(!readdata){
    act = Wire.read();
    switch(act){
       case 1: res=analogRead(0)/4;//0-255
        break;
       case 2: res=digitalRead(8)+1;//1,2
        break;
       case 3: sigont=1;res=1;
        break;
       case 4: digitalWrite(11,HIGH);res=1;
        break;
       case 5: digitalWrite(11,LOW);res=1;
        break;
       case 6: digitalWrite(12,HIGH);res=1;
        break;
       case 7: digitalWrite(12,LOW);res=1;
        break;
       case 8: shut();res=1;
        break;
       case 9: res=analogRead(1)/4;//0-255
        break;
       case 10: readdata=true;res=1;
        break;
    };
    act=0;
  }
  else{
    von = Wire.read();
    readdata=false;
    res=1;
    Serial.println(von);
  };
}

void sd(){
  Wire.write(res);
  res=0;
}

void shut(){
  //if(softofft==0){softofft=1;};
  softofft=1;
  int i=1;
  while(i<=3){
    digitalWrite(12,HIGH);
    digitalWrite(11,LOW);
    digitalWrite(10,LOW);
    delay(10000);
    digitalWrite(12,LOW);
    digitalWrite(11,HIGH);
    digitalWrite(10,LOW);
    delay(10000);
    digitalWrite(12,LOW);
    digitalWrite(11,LOW);
    digitalWrite(10,HIGH);
    delay(10000);
    digitalWrite(10,LOW);
    i++;
  }
}
