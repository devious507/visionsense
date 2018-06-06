// pass data from the MDB to the intermediary
// pass data from the intermediary to the MDB
int incByte0 = 0;
int incByte1 = 0;
int incByte2 = 0;
unsigned long myMillis = millis();
unsigned long tMillis = 10000;

void setup() {
  serialSetup();
  pinMode(53, OUTPUT);
  digitalWrite(53, HIGH);
  delay(20);
  digitalWrite(53, LOW);
}

void loop() {
  if (millis() > tMillis) {
    Serial.print(millis() - myMillis);
    Serial.print(" miliseconds have elapsed since last reset.  ");
    Serial.print("Pin 53 State: ");
    Serial.println(digitalRead(53));
    tMillis += 10000;
  }
  if (Serial.available() > 0) {
    incByte0 = Serial.read();
    Serial.print("I received on 0: (");
    Serial.print(incByte0, DEC);
    Serial.print(") ");
    Serial.println(char(incByte0));
    if (incByte0 == 82) {
      Serial.println("Resetting Watchdog!  WOOF WOOF");
      myMillis = millis();
      tMillis = myMillis;
      digitalWrite(53, HIGH);
      delay(20);
      Serial.print("Pin 53 State: ");
      Serial.println(digitalRead(53));
      digitalWrite(53, LOW);
    }
  }
  if (Serial1.available() > 0) {
    incByte1 = Serial1.read();
    Serial.print("I received on 1: ");
    Serial.println(incByte1, DEC);
  }
  if (Serial2.available() > 0) {
    incByte1 = Serial2.read();
    Serial.print("I received on 2: ");
    Serial.println(incByte1, DEC);
  }
}

void serialSetup() {
  Serial.begin(115200);
  while (!Serial) {
    ; // wait for serial port to become available
  }
  Serial.println("Serial Port Online!");
  Serial1.begin(9600);
  while (!Serial1) {
    ; // wait again
  }
  Serial.println("Serial1 Port Online!");
  Serial2.begin(9600);
  while (!Serial2) {
    ; // wait agian
  }
  Serial.println("Serial2 Port Online!");
  Serial.println();
  Serial.println();
  Serial.println();
}

