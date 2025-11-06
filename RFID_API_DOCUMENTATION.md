# RFID Attendance API Documentation

## Overview
This API provides endpoints for RFID-based attendance tracking in a kindergarten management system. It allows RFID card readers to automatically record student check-ins and check-outs.

## Base URL
```
http://your-domain.com/api/rfid
```

## Authentication
No authentication is required for these endpoints to allow seamless integration with hardware RFID readers.

---

## Endpoints

### 1. Scan RFID Card
Records attendance when an RFID card is scanned.

**Endpoint:** `POST /api/rfid/scan`

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "card_number": "RFID123456",
  "reader_id": "READER-01",     // Optional: Identifier of the RFID reader
  "location": "Main Entrance"    // Optional: Physical location of reader
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| card_number | string | Yes | The RFID card number to scan |
| reader_id | string | No | Unique identifier of the RFID reader device |
| location | string | No | Physical location where the scan occurred |

**Success Response (Check-In):**
```json
{
  "success": true,
  "message": "Welcome, John Doe! Checked in at 08:15 AM",
  "action": "check-in",
  "student": {
    "id": 1,
    "name": "John Doe",
    "email": "student1@example.com",
    "class": "Kindergarten A",
    "date_of_birth": "2019-05-15"
  },
  "attendance": {
    "id": 123,
    "date": "2025-11-06",
    "status": "present",
    "check_in_time": "08:15 AM",
    "check_out_time": null
  },
  "rfid": {
    "card_number": "RFID123456",
    "reader_id": "READER-01",
    "location": "Main Entrance"
  },
  "timestamp": "2025-11-06T08:15:30Z"
}
```

**Success Response (Check-Out):**
```json
{
  "success": true,
  "message": "Goodbye, John Doe! Checked out at 03:30 PM",
  "action": "check-out",
  "student": {
    "id": 1,
    "name": "John Doe",
    "email": "student1@example.com",
    "class": "Kindergarten A",
    "date_of_birth": "2019-05-15"
  },
  "attendance": {
    "id": 123,
    "date": "2025-11-06",
    "status": "present",
    "check_in_time": "08:15 AM",
    "check_out_time": "03:30 PM"
  },
  "rfid": {
    "card_number": "RFID123456",
    "reader_id": "READER-01",
    "location": "Main Entrance"
  },
  "timestamp": "2025-11-06T15:30:00Z"
}
```

**Error Response (Invalid Card):**
```json
{
  "success": false,
  "message": "Invalid or inactive RFID card",
  "card_number": "INVALID123",
  "timestamp": "2025-11-06T08:15:30Z"
}
```
**Status Code:** `404 Not Found`

**Error Response (Duplicate Scan):**
```json
{
  "success": false,
  "message": "John Doe has already checked in and out today",
  "action": "duplicate",
  "student": {
    "id": 1,
    "name": "John Doe",
    "email": "student1@example.com",
    "class": "Kindergarten A"
  },
  "attendance": {
    "date": "2025-11-06",
    "status": "present",
    "check_in_time": "08:15 AM",
    "check_out_time": "03:30 PM"
  },
  "timestamp": "2025-11-06T16:00:00Z"
}
```
**Status Code:** `200 OK`

**Error Response (Not a Student):**
```json
{
  "success": false,
  "message": "Card is not assigned to a student",
  "card_number": "RFID999999",
  "timestamp": "2025-11-06T08:15:30Z"
}
```
**Status Code:** `400 Bad Request`

**Error Response (Validation Error):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "card_number": [
      "The card number field is required."
    ]
  }
}
```
**Status Code:** `422 Unprocessable Entity`

---

### 2. Check Attendance Status
Retrieves the current attendance status for a student without creating a new record.

**Endpoint:** `POST /api/rfid/status`

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "card_number": "RFID123456"
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| card_number | string | Yes | The RFID card number to check |

**Success Response:**
```json
{
  "success": true,
  "student": {
    "id": 1,
    "name": "John Doe",
    "email": "student1@example.com",
    "class": "Kindergarten A"
  },
  "attendance": {
    "date": "2025-11-06",
    "status": "present",
    "check_in_time": "08:15 AM",
    "check_out_time": null
  },
  "timestamp": "2025-11-06T10:00:00Z"
}
```
**Status Code:** `200 OK`

**Response (No Attendance Today):**
```json
{
  "success": true,
  "student": {
    "id": 1,
    "name": "John Doe",
    "email": "student1@example.com",
    "class": "Kindergarten A"
  },
  "attendance": null,
  "timestamp": "2025-11-06T10:00:00Z"
}
```
**Status Code:** `200 OK`

**Error Response (Invalid Card):**
```json
{
  "success": false,
  "message": "Invalid or inactive RFID card"
}
```
**Status Code:** `404 Not Found`

---

### 3. Health Check
Verifies that the API is operational. Use this for monitoring RFID reader connectivity.

**Endpoint:** `GET /api/rfid/health`

**Request Headers:** None required

**Success Response:**
```json
{
  "success": true,
  "message": "RFID Attendance API is operational",
  "version": "1.0.0",
  "timestamp": "2025-11-06T08:00:00Z"
}
```
**Status Code:** `200 OK`

---

## Attendance Logic

### Check-In Rules
- First scan of the day = Check-in
- Recorded at current time
- Status automatically set to:
  - `present` if scanned before 8:30 AM
  - `late` if scanned after 8:30 AM

### Check-Out Rules
- Second scan of the day = Check-out
- Recorded at current time
- Status remains as previously set

### Duplicate Scans
- Third scan (after both check-in and check-out) returns an error
- No new attendance record is created
- Returns existing attendance data

---

## Attendance Status Types

| Status | Description |
|--------|-------------|
| `present` | Student arrived on time (before 8:30 AM) |
| `late` | Student arrived late (after 8:30 AM) |
| `absent` | Student did not check in (set manually) |
| `excused` | Student absence is excused (set manually) |

---

## Example Integration Code

### Python Example
```python
import requests
import json

# RFID Scan
def scan_rfid_card(card_number, reader_id=None, location=None):
    url = "http://your-domain.com/api/rfid/scan"
    payload = {
        "card_number": card_number,
        "reader_id": reader_id,
        "location": location
    }
    
    response = requests.post(url, json=payload)
    return response.json()

# Usage
result = scan_rfid_card("RFID123456", "READER-01", "Main Entrance")
print(result)
```

### JavaScript/Node.js Example
```javascript
async function scanRFIDCard(cardNumber, readerId = null, location = null) {
  const response = await fetch('http://your-domain.com/api/rfid/scan', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      card_number: cardNumber,
      reader_id: readerId,
      location: location
    })
  });
  
  return await response.json();
}

// Usage
scanRFIDCard('RFID123456', 'READER-01', 'Main Entrance')
  .then(data => console.log(data));
```

### cURL Example
```bash
# Scan RFID card
curl -X POST http://your-domain.com/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{
    "card_number": "RFID123456",
    "reader_id": "READER-01",
    "location": "Main Entrance"
  }'

# Check status
curl -X POST http://your-domain.com/api/rfid/status \
  -H "Content-Type: application/json" \
  -d '{"card_number": "RFID123456"}'

# Health check
curl http://your-domain.com/api/rfid/health
```

### Arduino/ESP32 Example
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

void scanRFIDCard(String cardNumber) {
  HTTPClient http;
  
  String url = "http://your-domain.com/api/rfid/scan";
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<200> doc;
  doc["card_number"] = cardNumber;
  doc["reader_id"] = "READER-01";
  doc["location"] = "Main Entrance";
  
  String requestBody;
  serializeJson(doc, requestBody);
  
  // Send request
  int httpResponseCode = http.POST(requestBody);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println(response);
    
    // Parse response
    StaticJsonDocument<1024> responseDoc;
    deserializeJson(responseDoc, response);
    
    bool success = responseDoc["success"];
    String message = responseDoc["message"];
    
    Serial.println(success ? "✓ " + message : "✗ " + message);
  } else {
    Serial.print("Error: ");
    Serial.println(httpResponseCode);
  }
  
  http.end();
}
```

---

## Error Handling

### HTTP Status Codes
| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful, but may contain error message (e.g., duplicate scan) |
| 201 | Created | New attendance record created successfully |
| 400 | Bad Request | Invalid request (e.g., card not for student) |
| 404 | Not Found | RFID card not found or inactive |
| 422 | Unprocessable Entity | Validation error in request data |
| 500 | Internal Server Error | Server error occurred |

### Error Response Format
All errors follow this format:
```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": {}, // Optional: validation errors
  "timestamp": "2025-11-06T08:15:30Z"
}
```

---

## Best Practices

### 1. **Handle Network Failures**
- Implement retry logic for failed requests
- Cache scans locally when offline
- Sync when connection is restored

### 2. **Validate Responses**
- Always check the `success` field
- Handle both success and error cases
- Log errors for debugging

### 3. **User Feedback**
- Display the `message` field to users
- Use audio/visual cues for check-in/check-out
- Show student name for confirmation

### 4. **Performance**
- Keep requests lightweight
- Use health check endpoint for connectivity monitoring
- Implement timeout handling (recommended: 5-10 seconds)

### 5. **Security Considerations**
- Use HTTPS in production environments
- Implement rate limiting on your RFID readers
- Validate card numbers before sending requests
- Consider adding API key authentication for production

---

## Testing

### Test RFID Cards
After seeding the database, you'll have 100 test RFID cards:
- Card numbers: `RFID0001` through `RFID0100`
- All cards are active and assigned to students

### Test Scenarios

#### 1. **Normal Check-In**
```bash
curl -X POST http://localhost/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"card_number":"RFID0001"}'
```

#### 2. **Check-Out (Run after check-in)**
```bash
curl -X POST http://localhost/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"card_number":"RFID0001"}'
```

#### 3. **Duplicate Scan**
```bash
# Run this after both check-in and check-out
curl -X POST http://localhost/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"card_number":"RFID0001"}'
```

#### 4. **Invalid Card**
```bash
curl -X POST http://localhost/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"card_number":"INVALID999"}'
```

#### 5. **Check Status**
```bash
curl -X POST http://localhost/api/rfid/status \
  -H "Content-Type: application/json" \
  -d '{"card_number":"RFID0001"}'
```

---

## Troubleshooting

### Common Issues

#### 1. **404 Error on All Requests**
- Verify the API route is registered in `routes/api.php`
- Check that the base URL includes `/api/` prefix
- Ensure the Laravel application is running

#### 2. **CORS Errors (Browser/Web Clients)**
- Configure CORS in `config/cors.php`
- Add your RFID reader's IP to allowed origins

#### 3. **Card Not Found**
- Verify the card exists in the database
- Check that card status is 'active'
- Ensure card is assigned to a user with 'student' role

#### 4. **Slow Response Times**
- Check database indexing on `card_number` field
- Optimize network connection
- Consider caching RFID card data

---

## Rate Limiting

Currently, no rate limiting is applied. For production:
- Recommend implementing rate limiting
- Suggested: 60 requests per minute per reader
- Use `reader_id` to track per-device limits

---

## Changelog

### Version 1.0.0 (November 6, 2025)
- Initial release
- Basic check-in/check-out functionality
- Status check endpoint
- Health check endpoint
- Late detection (after 8:30 AM)
- Duplicate scan prevention

---

## Support

For issues or questions:
- Check the troubleshooting section above
- Review Laravel logs: `storage/logs/laravel.log`
- Verify database connectivity
- Test with the web simulator at `/rfid`

---

## License

This API is part of the Kindergarten Management System.
