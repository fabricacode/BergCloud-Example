#include <BERGCloud.h>
#include <SPI.h>

#define PRODUCT_VERSION 0
const uint8_t PRODUCT_KEY[16] = {
  0xD8,0x5E,0x54,0xC0,0xE4,0x5A,0xE9,0x5F,0x03,0xED,0xAE,0x36,0x34,0x3C,0xD5,0x67};
#define nSSEL_PIN 10

#define DEFAULT_EVENT 1
#define DEFAULT_COMMAND 1

#define ANALOG_DIFF 20
#define DELAY_TIME 300
#define digitalPinsCount 12
#define analogPinsCount 6

int digitalPins[] = {
  OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT};   
int analogPins[] =  {
  INPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT,OUTPUT}; 

int analogCurrentValues[analogPinsCount];      // an array of pin numbers to which LEDs are attached
int digitalCurrentValues[digitalPinsCount];      // an array of pin numbers to which LEDs are attached



void setup()
{
  Serial.begin(115200);
  BERGCloud.begin(&SPI, nSSEL_PIN);
  checkNetworkState();

  for(int i=0; i<digitalPinsCount;i++){
    pinMode(i, digitalPins[i]);
    digitalCurrentValues[i]=-1;
  }
  for(int i=0; i<analogPinsCount;i++){
    pinMode(i, analogPins[i]);
    analogCurrentValues[i]=-1000;
  }
}




void loop()
{
  uint32_t data;
  uint8_t a;
  uint8_t eui64[BC_EUI64_SIZE_BYTES];
  char claimcode[BC_CLAIMCODE_SIZE_BYTES];
  uint8_t temp[10];
  uint32_t i;
  int8_t rssi;
  uint8_t lqi;
  if(checkNetworkState()){
    pollForCommand();
    delay(DELAY_TIME);
    for(int i=0; i<analogPinsCount;i++){
      if(analogPins[i]==INPUT){
        int currentValue=analogRead(i);
        if(abs(currentValue-analogCurrentValues[i])>ANALOG_DIFF){
          temp[0] = 'A';
          temp[1] = i;
          temp[5] = currentValue>> 24;
          temp[4] = currentValue>> 16;
          temp[3] = currentValue>> 8;
          temp[2] = currentValue;

          if (BERGCloud.sendEvent(DEFAULT_EVENT, temp, 6)){
            Serial.println("Analog Event sent");
            Serial.println(currentValue);
            analogCurrentValues[i]= currentValue;
          }
          else{
            Serial.println("failed/busy");
          }
        }
      }
    }

    for(int i=0; i<digitalPinsCount;i++){
      if(digitalPins[i]==INPUT){
        int currentValue=digitalRead(i);
        if(currentValue!=digitalCurrentValues[i]){
          temp[0] = 'D';
          temp[1] = i;
          temp[2] = currentValue;
          if (BERGCloud.sendEvent(DEFAULT_EVENT, temp, 3)){
            Serial.println("Event sent");
            digitalCurrentValues[i]= currentValue;
          }
          else{
            Serial.println("failed/busy");
          }
        }
      }
    }
  }
  else{
    delay(3000);
  }
}


void pollForCommand(){

  uint8_t commandBuffer[20];
  uint16_t commandSize;
  uint8_t commandID;
  if (BERGCloud.pollForCommand(commandBuffer, sizeof(commandBuffer), &commandSize, &commandID))
  {
    
    Serial.print("Got command 0x");
    Serial.print(commandID, HEX);
    Serial.print(" with data length ");
    Serial.print(commandSize, DEC);
    Serial.println(" bytes.");

    if(commandSize==4 && commandBuffer[0]=='D'){
      commandBuffer[4]='\n';
      int value= atoi((char *)&commandBuffer[3]);
      commandBuffer[3]='\n';
      int pin=atoi((char *)&commandBuffer[1]);
      Serial.print("PIN ");
      Serial.print(pin);
      Serial.print(" VALUE ");
      Serial.print(value);
      Serial.println(" DIGITAL");
      if(pin<digitalPinsCount && digitalPins[pin]==OUTPUT){
        digitalWrite(pin,value);
      }

    }

    if(commandSize==7 && commandBuffer[0]=='A'){
      commandBuffer[7]='\n';
      int value= atoi((char *)&commandBuffer[3]);
      commandBuffer[3]='\n';
      int pin=atoi((char *)&commandBuffer[1]);
      Serial.print("PIN ");
      Serial.print(pin);
      Serial.print(" VALUE ");
      Serial.print(value);
      Serial.println("  ANALOG");
      if(pin<analogPinsCount && analogPins[pin]==OUTPUT){
        analogWrite(pin,value);
      }
    }
  }

}



boolean checkNetworkState(){
  //CHECK NETWORK STATUS AND RECONNECT IF NEEDED
  uint8_t a;
  if (BERGCloud.getNetworkState(&a))
  {
    switch(a){
    case BC_NETWORK_STATE_CONNECTED:
     // Serial.println("Network State: connected!");
      return true;
    case BC_NETWORK_STATE_CONNECTING:
      Serial.println("Network State: connecting!");
      return false;
    case BC_NETWORK_STATE_DISCONNECTED:
      Serial.println("Network State: disconnected!");
      break;
    default:
      Serial.println("Network State: Unknown!");
      break;
    }
  }
  else{
    Serial.println("getNetworkState() returned false.");
  }
  if (BERGCloud.joinNetwork(PRODUCT_KEY, PRODUCT_VERSION)){
    Serial.println("Joined/Rejoined network");
  }
  else{
    Serial.println("joinNetwork() returned false.");
    
  }
  return false;
}


