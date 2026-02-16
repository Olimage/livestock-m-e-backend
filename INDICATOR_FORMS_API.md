# Indicator Forms API Documentation

## Overview
The Indicator Forms API allows users to submit form data with indicator values. Form fields are named after indicator slugs, and values correspond to the indicator's measurement unit.

## Endpoints

### 1. List All Indicator Forms
**GET** `/api/v1/indicator-forms`

Query Parameters:
- `per_page` (optional): Number of items per page (default: 15)
- `page` (optional): Page number (default: 1)

**Example Request:**
```bash
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/v1/indicator-forms?per_page=20"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "data": {
          "proportion_of_livestock_households_below_poverty_line": "50",
          "daily_animal_protein_consumption_per_capita": "75.5"
        },
        "submitted_at": "2026-02-16T12:30:00Z",
        "created_at": "2026-02-16T12:30:00Z",
        "updated_at": "2026-02-16T12:30:00Z"
      }
    ],
    "total": 10,
    "per_page": 15,
    "last_page": 1
  }
}
```

---

### 2. Submit Indicator Form
**POST** `/api/v1/indicator-forms`

**Request Body:**
Submit indicator values using indicator slugs as field names. Values should correspond to the indicator's measurement unit.

```json
{
  "proportion_of_livestock_households_below_poverty_line": "50",
  "proportion_of_population_with_access_to_livestock_products": "75.5",
  "consumer_price_index_for_livestock_products": "120",
  "daily_animal_protein_consumption_per_capita": "85.3"
}
```

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/indicator-forms \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "proportion_of_livestock_households_below_poverty_line": "50",
    "daily_animal_protein_consumption_per_capita": "75.5"
  }'
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Indicator form submitted successfully.",
  "data": {
    "id": 1,
    "submitted_at": "2026-02-16T12:30:00Z",
    "values": {
      "proportion_of_livestock_households_below_poverty_line": {
        "value": "50",
        "unit": "Percentage",
        "indicator_code": "IMP-1",
        "indicator_name": "Proportion of livestock households below poverty line"
      },
      "daily_animal_protein_consumption_per_capita": {
        "value": "75.5",
        "unit": "Kilogrammes (Kg)",
        "indicator_code": "OUT-7",
        "indicator_name": "Daily Animal Protein Consumption Per Capita"
      }
    }
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "invalid_indicator_slug": "Invalid indicator slugs: invalid_slug_1, invalid_slug_2",
    "proportion_of_livestock_households_below_poverty_line": "Value must be numeric (Measurement unit: Percentage)"
  }
}
```

**Validation Rules:**
- All submitted field names must be valid indicator slugs
- All values must be numeric (for most measurement units)
- At least one indicator value is required
- Empty/null values are skipped

---

### 3. Get Indicator Form Details
**GET** `/api/v1/indicator-forms/{id}`

**Example Request:**
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost:8000/api/v1/indicator-forms/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "submitted_at": "2026-02-16T12:30:00Z",
    "created_at": "2026-02-16T12:30:00Z",
    "updated_at": "2026-02-16T12:30:00Z",
    "values": {
      "proportion_of_livestock_households_below_poverty_line": {
        "value": "50",
        "unit": "Percentage",
        "indicator_code": "IMP-1",
        "indicator_name": "Proportion of livestock households below poverty line"
      }
    }
  }
}
```

---

### 4. Update Indicator Form
**PUT/PATCH** `/api/v1/indicator-forms/{id}`

**Request Body:**
```json
{
  "proportion_of_livestock_households_below_poverty_line": "55",
  "daily_animal_protein_consumption_per_capita": "80"
}
```

**Example Request:**
```bash
curl -X PUT http://localhost:8000/api/v1/indicator-forms/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "proportion_of_livestock_households_below_poverty_line": "55"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Indicator form updated successfully.",
  "data": {
    "id": 1,
    "submitted_at": "2026-02-16T13:45:00Z"
  }
}
```

---

### 5. Delete Indicator Form
**DELETE** `/api/v1/indicator-forms/{id}`

**Example Request:**
```bash
curl -X DELETE http://localhost:8000/api/v1/indicator-forms/1 \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "success": true,
  "message": "Indicator form deleted successfully."
}
```

---

## Authentication
All endpoints require JWT authentication. Include the token in the `Authorization` header:
```
Authorization: Bearer {your_jwt_token}
```

## Data Structure

### IndicatorForm Model
- `id` (integer): Primary key
- `user_id` (integer, nullable): Foreign key to users table
- `data` (JSON): Key-value pairs of indicator slugs and their values
- `submitted_at` (timestamp, nullable): When the form was submitted
- `created_at` (timestamp): When the record was created
- `updated_at` (timestamp): When the record was last updated

### Database Table
```sql
CREATE TABLE indicator_forms (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NULLABLE,
  data JSON,
  submitted_at TIMESTAMP NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## Example Usage

### Submit a Form with Multiple Indicators
```bash
curl -X POST http://localhost:8000/api/v1/indicator-forms \
  -H "Authorization: Bearer eyJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "proportion_of_livestock_households_below_poverty_line": "45.5",
    "daily_animal_protein_consumption_per_capita": "82.3",
    "total_national_meat_production_all_species": "120000",
    "total_national_milk_production": "250000"
  }'
```

### Retrieve All Forms
```bash
curl -H "Authorization: Bearer eyJhbGc..." \
  "http://localhost:8000/api/v1/indicator-forms?per_page=50&page=1"
```

### Get a Specific Form
```bash
curl -H "Authorization: Bearer eyJhbGc..." \
  http://localhost:8000/api/v1/indicator-forms/5
```

---

## Notes
- Form values are automatically enriched with indicator metadata on response (unit, code, name)
- Only authenticated users can submit forms
- Forms are automatically timestamp-aware
- The API validates that all submitted slugs match existing indicators
- Invalid indicators receive a 422 status with detailed error messages
