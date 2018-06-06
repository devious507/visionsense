// This sketch is designed for the 5v MEGA
// 3.3V DUE may cause issue, so we're gonna go with the mega
// complete with 4 interruptable intrusion sensors (18,19,20,21)
// 2 standard intrusion sensors (loop processed) (22,23);
// electrical processing on A0 and A1
// OneWire Reading on 2
// Water Monitoring (Interrupt) on 3

#include <SPI.h>
#include <SD.h>
#include <Dhcp.h>
#include <Dns.h>
#include <Ethernet.h>
#include <EthernetClient.h>
#include <HttpClient.h>
#include <EthernetUdp.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <utility/w5100.h>



// Dallas Semiconductor ONE_WIRE Stuff
// Data wire is plugged into pin 2 on the Arduino
#define ONE_WIRE_BUS 2
#define PIN3DEBOUNCE 0
#define PIN18DEBOUNCE 25
#define PIN19DEBOUNCE 25
#define PIN22DEBOUNCE 25
#define PIN23DEBOUNCE 25
#define PIN24DEBOUNCE 25
#define PIN25DEBOUNCE 25
#define DEBUG false
#define DEBUG_POWER false
#define DEBUG_WATER false
#define SOCKET_DEBUG false
#define DEBUG_INTERVAL 20000

byte socketStat[MAX_SOCK_NUM];
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

// Ethernet Stuff
EthernetClient client;

byte mac[] = {0x00, 0x02, 0x00, 0xC0, 0xFF, 0xEE};
char server[] = "collector.rtmscloud.com";
char host[] = "HOST: collector.rtmscloud.com";
char qsFormat[] = "GET /all.php?mac=%.2X%.2X.%.2X%.2X.%.2X%.2X&water=%d&electric=%d&temp1=%d&temp2=%d&temp3=%d&temp4=%d&temp5=%d&temp6=%d HTTP/1.1";
char qsFormatR[] = "GET /all.php?mac=%.2X%.2X.%.2X%.2X.%.2X%.2X&water=%d&electric=%d&temp1=%d&temp2=%d&temp3=%d&temp4=%d&temp5=%d&temp6=%d&reset=true HTTP/1.1";
char qsSingle[] = "GET /sensor.php?mac=%.2X%.2X.%.2X%.2X.%.2X%.2X&sensor=%d&value=%d HTTP/1.1";

// Used loop control
unsigned long prevMillis = millis();
// Used for debouncing interrupts
unsigned long pin2Millis = millis();
unsigned long pin3Millis = millis();
unsigned long pin18Millis = millis();
unsigned long pin19Millis = millis();
unsigned long pin22Millis = millis();
unsigned long pin23Millis = millis();
unsigned long pin24Millis = millis();
unsigned long pin25Millis = millis();
unsigned long interval = 290000;

int temperatures[6] = {0, 0, 0, 0, 0, 0};
volatile int waterPulses = 0;
double electric = 0;
double RMSCurrentFactor = 8.2377;
int pin22State;
int pin23State;
int pin24State;
int pin25State;

void setup() {
  if (DEBUG) {
    interval = DEBUG_INTERVAL;
  }
  serialSetup();            // Initialize Serial Port
  pinMode(53, OUTPUT);
  Serial.println("Resetting Watchdog as part of boot process");
  digitalWrite(53, HIGH);
  delay(20);
  digitalWrite(53, LOW);
  sdSetup();                // Get ready to read from SD
  readMacFromSD();          // Read the mac address from SD, defaults to 02:00:00:C0:FF:EE
  ethernetSetup();          // DHCP initialize the ethernet port
  if (DEBUG) {
    delay(5000);
  }
  getAdjustmentFromWeb();   // Use our brand new network to get the RMS Adjustment Factor from the web.
  Serial.print("Loop Timer set to: ");
  Serial.println(interval);
  Serial.println("---------------------------------------------------------------");
  pinMode(2, INPUT);              // Dallas OneWire / Temperature Probes
  pinMode(3, INPUT);              // Water Meter, Interrupt Driven
  pinMode(18, INPUT);      // Interrupt Driven
  pinMode(19, INPUT);      // Interrupt Driven
  pinMode(22, INPUT);      // Poll Driven
  pinMode(23, INPUT);      // Poll Driven
  pinMode(24, INPUT);      // Poll Driven
  pinMode(25, INPUT);      // Poll Driven
  pin22State = digitalRead(22);
  pin23State = digitalRead(23);
  pin24State = digitalRead(24);
  pin25State = digitalRead(25);
  attachInterrupt(digitalPinToInterrupt(3), pin3ISR, CHANGE);
  attachInterrupt(digitalPinToInterrupt(18), pin18ISR, CHANGE);
  attachInterrupt(digitalPinToInterrupt(19), pin19ISR, CHANGE);
  myMillisEvents(true);
  Serial.println("Done with setup stuff:   Entering main processing loop");
  Serial.println("---------------------------------------------------------------");
}

void loop() {
  // put your main code here, to run repeatedly:
  dhcpStuff();
  electricalProcessing();
  if (millis() - prevMillis >= interval) {
    myMillisEvents(false);
  }
  if (DEBUG) {
    Serial.print("TOGO (ms):  ");
    Serial.println(interval - (millis() - prevMillis));
    Serial.print("-------------------------------------------------");
    Serial.println(digitalRead(18));
  }
  int myState = digitalRead(22);
  if ((myState != pin22State) && (millis() - pin22Millis >= PIN22DEBOUNCE)) {
    pin22State = myState;
    if (DEBUG)
      Serial.println("pin 22 flipped");
    sendSensorState(22, myState);
  }
  myState = digitalRead(23);
  if ((myState != pin23State) && (millis() - pin23Millis >= PIN23DEBOUNCE)) {
    pin23State = myState;
    if (DEBUG)
      Serial.println("pin 23 flipped");
    sendSensorState(23, myState);
  }
  myState = digitalRead(24);
  if ((myState != pin24State) && (millis() - pin24Millis >= PIN24DEBOUNCE)) {
    pin24State = myState;
    if (DEBUG)
      Serial.println("pin 24 flipped");
    sendSensorState(24, myState);
  }
  myState = digitalRead(25);
  if ((myState != pin25State) && (millis() - pin25Millis >= PIN25DEBOUNCE)) {
    pin25State = myState;
    if (DEBUG)
      Serial.println("pin 25 flipped");
    sendSensorState(25, myState);
  }
  if (SOCKET_DEBUG) {
    ShowSockStatus();
  }
}

void sdSetup() {
  Serial.print("Initializing SD card...");
  if (!SD.begin(4)) {
    Serial.println("initialization failed!");
  } else {
    Serial.println("initialization done.");
  }
}
void electricalProcessing() {
  // Electrical Processing stuff
  int current = 0 ;
  int current2 = 0 ;
  int maxCurrent = 0;
  int maxCurrent2 = 0;
  for (int i = 0; i <= 200; i++) {
    current = analogRead(A0);
    current2 = analogRead(A1);
    if (current > maxCurrent) {
      maxCurrent = current;
    }
    if (maxCurrent <= 517) {
      maxCurrent = 516;
    }
    if (current2 > maxCurrent2) {
      maxCurrent2 = current2;
    }
    if (maxCurrent2 <= 517) {
      maxCurrent2 = 516;
    }
  }
  double RMSCurrent = ((maxCurrent - 516) * 0.707) / RMSCurrentFactor;
  double RMSCurrent2 = ((maxCurrent2 - 516) * 0.707) / RMSCurrentFactor;
  int RMSPower = 110 * RMSCurrent;
  int RMSPower2 = 110 * RMSCurrent2;
  electric = electric + (RMSPower * (2.05 / 60 / 60));
  electric = electric + (RMSPower2 * (2.05 / 60 / 60));
  if (DEBUG_POWER) {
    Serial.print("A0 current -- ");
    Serial.println(maxCurrent);
    Serial.print("A0 Amps -- ");
    Serial.println(RMSCurrent);
    Serial.print("A0 RMS Power --");
    Serial.println(RMSPower);

    Serial.print("A1 current -- ");
    Serial.println(maxCurrent2);
    Serial.print("A1 Amps -- ");
    Serial.println(RMSCurrent2);
    Serial.print("A1 RMS Power --");
    Serial.println(RMSPower2);
    Serial.print("wH -- ");
    Serial.println(electric);
  }
  unsigned long emillis = millis();
  while (millis() - emillis < 2000) {
    delay(1);
  }
}

void pin3ISR() {
  if (millis() - pin3Millis > PIN3DEBOUNCE) {
    waterPulses++;
    pin3Millis = millis();
    if (DEBUG) {
      Serial.println("Water Pulsed");
    }
  }
}
void pin18ISR() {
  if (millis() - pin18Millis > PIN18DEBOUNCE) {
    sendSensorState(18, digitalRead(18));
    pin18Millis = millis();
  }
}
void pin19ISR() {
  if (millis() - pin19Millis > PIN19DEBOUNCE) {
    sendSensorState(19, digitalRead(19));
    pin19Millis = millis();
    if (DEBUG) {
      Serial.println("PIN8 Tripped");
    }
  }
}
void sendSensorState(int num, int val) {
  // code to send a single sensor change goes here.
  char tmp[256];
  int mynum = num;
  switch (num) {
    case 18:
      mynum = 1;
      break;
    case 19:
      mynum = 2;
      break;
    case 22:
      mynum = 3;
      break;
    case 23:
      mynum = 4;
      break;
    case 24:
      mynum = 5;
      break;
    case 25:
      mynum = 6;
      break;
  }
  sprintf(tmp, qsSingle, mac[0], mac[1], mac[2], mac[3], mac[4], mac[5], mynum, val);
  doHttpRequest(tmp);
}
void myMillisEvents(bool isReset) {
  Serial.println("Loop Started"
                );
  char tmp[256];
  if (DEBUG) {
    Serial.print(interval / 1000);
    Serial.print(" seconds have elapsed: waterPulses=");
    Serial.println(waterPulses);
  }
  sensors.requestTemperatures();
  // Delay here is to allow requestTemperatures to return, prevents sampling lag and
  // Abnormally high temperature reports on system boot.
  unsigned long tMillis = millis();
  while (millis() - tMillis <= 1000) {
    ; // do nothing
  }
  temperatures[0] = sensors.getTempFByIndex(0);
  temperatures[1] = sensors.getTempFByIndex(1);
  temperatures[2] = sensors.getTempFByIndex(2);
  temperatures[3] = sensors.getTempFByIndex(3);
  temperatures[4] = sensors.getTempFByIndex(4);
  temperatures[5] = sensors.getTempFByIndex(5);
  if (isReset) {
    sprintf(tmp, qsFormatR, mac[0], mac[1], mac[2], mac[3], mac[4], mac[5], waterPulses, (int)electric, temperatures[0], temperatures[1], temperatures[2], temperatures[3], temperatures[4], temperatures[5]);
  } else {
    sprintf(tmp, qsFormat, mac[0], mac[1], mac[2], mac[3], mac[4], mac[5], waterPulses, (int)electric, temperatures[0], temperatures[1], temperatures[2], temperatures[3], temperatures[4], temperatures[5]);
  }
  if (DEBUG) {
    Serial.print(electric);
    Serial.println(" Wh Used since last update.");
  }
  waterPulses = 0;
  electric = 0;
  //  setPinStateArray(false);
  prevMillis = millis();
  bool resp;
  resp = doHttpRequest(tmp);
  if (resp == true) {
    Serial.println("Resetting Watchdog on 5minute success");
    digitalWrite(53, HIGH);
    delay(20);
    digitalWrite(53, LOW);
  } else {
    if (DEBUG) {
      Serial.println();
      Serial.println();
      Serial.println(resp);
    }
  }
  sendSensorState(18, digitalRead(18));
  sendSensorState(19, digitalRead(19));
  sendSensorState(22, digitalRead(22));
  sendSensorState(23, digitalRead(23));
  sendSensorState(24, digitalRead(24));
  sendSensorState(25, digitalRead(25));

}
void serialSetup() {
  Serial.begin(115200);
  while (!Serial) {
    ; // wait for serial port to become available
  }
  Serial.println("Serial Port Online!");
}

void ethernetSetup() {
  // start the Ethernet connection:
  unsigned long tMillis = millis();
  while (Ethernet.begin(mac) == 0) {
    Serial.println(" Failed to configure Ethernet using DHCP");
    while (millis() - tMillis <= 10000) {
      ; // do nothing 10 second delay
    }
    softReset();
  }
  Serial.println(host);
  // print your local IP address:
  printIPAddress();
}

void printHex(int num, int precision) {
  char tmp[16];
  char format[128];
  sprintf(format, "%%.%dX", precision);
  sprintf(tmp, format, num);
  Serial.print(tmp);
}

void printIPAddress()
{
  Serial.print("My IP address: ");
  for (byte thisByte = 0; thisByte < 4; thisByte++) {
    // print the value of each byte of the IP address:
    Serial.print(Ethernet.localIP()[thisByte], DEC);
    Serial.print(".");
  }
  Serial.println();
}

void dhcpStuff() {
  switch (Ethernet.maintain())
  {
    case 1:
      //renewed fail
      Serial.println("Error: renewed fail");
      break;
    case 2:
      //renewed success
      Serial.println("Renewed success");
      //print your local IP address:
      printIPAddress();
      break;
    case 3:
      //rebind fail
      Serial.println("Error: rebind fail");
      break;
    case 4:
      //rebind success
      Serial.println("Rebind success");
      //print your local IP address:
      printIPAddress();
      break;
    default:
      //nothing happened
      break;
  }
}

void getAdjustmentFromWeb() {
  Serial.println("Waiting for TCP...");
  delay(2000);
  int err = 0;
  char tmp[] = "0000000000";
  char url[256];
  char url_tmp[] = "/getRmsAdjust.php?mac=%.2X%.2X.%.2X%.2X.%.2X%.2X";
  char hostname[] = "my.rtmscloud.com";
  const int kNetworkTimeout = 10 * 1000;
  sprintf(url, url_tmp, mac[0], mac[1], mac[2], mac[3], mac[4], mac[5]);
  Serial.println(url);
  HttpClient http(client);
  err = http.get(hostname, url);
  if ( (err >= 0) && (DEBUG) ) {
    Serial.println("started Request: OK");
  }
  err = http.responseStatusCode();
  if ( (err >= 0) && (DEBUG)) {
    Serial.print("Received responseStatusCode = ");
    Serial.println(err);
  }
  err = http.skipResponseHeaders();
  if ( (err >= 0) && (DEBUG) ) {
    Serial.println("Skipped Headers.");
  }
  int bodyLen = http.contentLength();
  char c;
  int count = 0;
  unsigned long timeoutStart = millis();
  while (http.available() && ((millis() - timeoutStart) < kNetworkTimeout)) {
    c = http.read();
    tmp[count] = c;
    count++;
  }
  http.stop();
  RMSCurrentFactor = atof(tmp);
  if (RMSCurrentFactor == 0) {
    if (DEBUG) {
      Serial.println("Network read of RMSCurrentFactor failed... rebooting");
      Serial.println();
      Serial.println();
      delay(100);
    }
    softReset();
  }
  Serial.println("RMSCurrent Factor Read from WEB");
  Serial.print("RMSCurrent Factor Set to: ");
  Serial.println(RMSCurrentFactor, 6);
  Serial.println("---------------------");
}

void readMacFromSD() {
  if (SD.exists("mac.txt")) {
    Serial.println("mac.txt exists.");
    File datafile = SD.open("mac.txt");
    char *c = "0x00";
    byte b1 = 0x02;
    byte b2;
    int n;
    char colon;
    int count = 0;
    if (datafile) {
      while (datafile.available()) {
        if (count <= 5) {
          c[2] = datafile.read();
          c[3] = datafile.read();
          colon = datafile.read();
          sscanf(c, "%x", &n);
          mac[count] = n;
        }
        count++;
      }
      Serial.println("file read");
      datafile.close();
    }
  } else {
    Serial.println("mac.txt doesn't exist.");
  }
  Serial.print("MAC Address:  ");
  for (byte thisByte = 0; thisByte < 6; thisByte++) {
    // print the value of each byte of the IP address:
    printHex(mac[thisByte], 2);
    if (thisByte != 5) {
      Serial.print(":");
    }
  }
  Serial.println();
  Serial.println("---------------------");
}

void ShowSockStatus()
{
  for (int i = 0; i < MAX_SOCK_NUM; i++) {
    Serial.print(F("Socket#"));
    Serial.print(i);
    uint8_t s = W5100.readSnSR(i);
    socketStat[i] = s;
    Serial.print(F(":0x"));
    Serial.print(s, 16);
    Serial.print(F(" "));
    Serial.print(W5100.readSnPORT(i));
    Serial.print(F(" D:"));
    uint8_t dip[4];
    W5100.readSnDIPR(i, dip);
    for (int j = 0; j < 4; j++) {
      Serial.print(dip[j], 10);
      if (j < 3) Serial.print(".");
    }
    Serial.print(F("("));
    Serial.print(W5100.readSnDPORT(i));
    Serial.println(F(")"));
  }
}

bool doHttpRequest(char tmp[256])
{
  int inChar;
  int count = 1;
  int result = 0;
  while (result != 1) {
    result = client.connect(server, 80);
    if (result != 1) {
      Serial.print("Connection Failure: Reason -- ");
    }
    switch (result) {
      case 1:
        break;
      case -1:
        Serial.println("TIMED OUT");
        delay(2000);
        break;
      case -2:
        Serial.println("INVALID SERVER");
        delay(2000);
        break;
      case -3:
        Serial.println("TRUNCATED");
        delay(2000);
        break;
      case -4:
        Serial.println("INVALID RESPONSE");
        delay(2000);
        break;
      default:
        Serial.print(result);
        Serial.println(" -- UNKNOWN REASON");
        delay(2000);
        break;
    }
    if (count >= 5) {
      if (DEBUG) {
        Serial.println();
        Serial.println("Rebooting...");
        Serial.println();
        delay(200);
      }
      softReset();
    }
    if (result != 1) {
      Serial.print("Failure Count: ");
      Serial.println(count);
      count++;
    }
  }
  // Print the HTTP request to the serial port
  if (DEBUG) {
    Serial.println(tmp);
  }
  // Make a HTTP request:
  client.println(tmp);
  client.println(host);
  while (client.available()) {
    inChar = client.read();
    if (DEBUG) {
      Serial.print(inChar);
    }
  }
  client.println("Connection: close");
  client.println();
  client.stop();
  if (result == 1) {
    return true;
  } else {
    return false;
  }
}

void softReset() {
  bool soft = true;
  if (soft == true) {
    asm volatile ("  jmp 0");
  } else {
    while (1) {
      // intentionally hang the program, will watchdog reset after approximately 1 minutes
    }
  }
}

