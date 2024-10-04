#include <SPI.h>
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ZMPT101B.h>
#include <DHT.h>

// Pin definitions
#define VOLTAGE_SENSOR_PIN 34  // ZMPT101B sensor connected to GPIO34 (ADC1 Channel 6)
#define HALL_SENSOR_PIN 35     // Hall effect sensor connected to GPIO35
#define DHT_SENSOR_PIN 32      // DHT sensor connected to GPIO32
#define CURRENT_SENSOR_PIN 33  // ZMCT103C current sensor connected to GPIO33 (ADC1 Channel 5)
#define SOUND_SENSOR_PIN 25    // Sound sensor connected to GPIO25
#define DHTTYPE DHT11          // DHT11 sensor (use DHT22 if you have it)

// Wi-Fi credentials
const char* ssid = "nupenndralap";
const char* password = "nupenndralap";

// Server IP address
const char* server = "192.168.146.131"; // Update to your server's IP address
WiFiClient client;

// Calibration values for ZMPT101B (Voltage Sensor)
float SENSITIVITY_VOLTAGE = 110.0f;  
float SCALING_FACTOR_VOLTAGE = 3.20f;  

// Calibration values for ZMCT103C (Current Sensor)
float SENSITIVITY_CURRENT = 30.0f;  
float SCALING_FACTOR_CURRENT = 1.0f;  

// Variables for RPM calculation (Hall Effect Sensor)
volatile int pulseCount = 0;
unsigned long lastTime = 0;
unsigned long rpmUpdateTime = 1000;  
float rpm = 0.0;

// ZMPT101B sensor initialized with voltage source frequency of 50Hz
ZMPT101B voltageSensor(VOLTAGE_SENSOR_PIN, 50.0);
DHT dht(DHT_SENSOR_PIN, DHTTYPE);

// Interrupt service routine (ISR) to count pulses from Hall effect sensor
void IRAM_ATTR onPulse() {
  pulseCount++;
}

void setup() {
  Serial.begin(115200);
  voltageSensor.setSensitivity(SENSITIVITY_VOLTAGE);
  pinMode(HALL_SENSOR_PIN, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(HALL_SENSOR_PIN), onPulse, RISING);
  dht.begin();

  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  unsigned long currentTime = millis();
  
  if (currentTime - lastTime >= rpmUpdateTime) {
    detachInterrupt(digitalPinToInterrupt(HALL_SENSOR_PIN));  
    
    // RPM calculation
    rpm = (pulseCount * 60.0) / (rpmUpdateTime / 1000.0);
    pulseCount = 0;
    lastTime = currentTime;
    attachInterrupt(digitalPinToInterrupt(HALL_SENSOR_PIN), onPulse, RISING);
  }
  
  // Read RMS voltage
  float rawVoltage = voltageSensor.getRmsVoltage();
  float voltage = rawVoltage * SCALING_FACTOR_VOLTAGE;

  // Read temperature and humidity
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();

  // Read raw current value
  int rawCurrent = analogRead(CURRENT_SENSOR_PIN);
  float current = (rawCurrent / 4095.0) * 3.3;
  current = (current - 1.65) / SENSITIVITY_CURRENT;
  current *= SCALING_FACTOR_CURRENT;

  // Read sound sensor value
  int soundValue = 0; 
  for (int i = 0; i < 32; i++) {
    soundValue += analogRead(SOUND_SENSOR_PIN); 
  }
  soundValue >>= 5;

  // Send data to PHP script
  Sending_To_phpmyadmindatabase(rpm, current, voltage, soundValue, temperature);

  delay(1000);
}

void Sending_To_phpmyadmindatabase(float rpm, float current, float voltage, int soundValue, float temperature) {
  if (client.connect(server, 80)) {
    Serial.println("Connected to server");

    // Make an HTTP GET request
    String url = String("/AkanshMajorProject/connect.php?rpm_value=") + rpm + 
                 "&current_value=" + current + 
                 "&voltage_value=" + voltage + 
                 "&noise_value=" + soundValue + 
                 "&temperature_value=" + temperature;

    Serial.println("Request URL: " + url);
    
    // Send the HTTP GET request
    client.print("GET " + url + " HTTP/1.1\r\n");
    client.print("Host: " + String(server) + "\r\n");
    client.print("Connection: close\r\n");
    client.print("\r\n");
    
    // Wait for server response
    while (client.available() == 0) {
      // Wait for data from server
    }

    // Read the response
    while (client.available()) {
      String line = client.readStringUntil('\n');
      Serial.println(line);
    }

  } else {
    Serial.println("Connection failed");
  }
  client.stop(); // Close the connection
}
