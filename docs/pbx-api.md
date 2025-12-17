# PBX API (Phase A/D)

Base URL: `https://your-domain.com`

## Validate Call Card
- `GET /api/pbx/validate?token={card_uuid}`
- Success: `{"valid":true,"card_uuid":"<uuid>","minutes_left":87,"prefix":"+234","status":"active"}`
- Not found: `{"valid":false,"reason":"not_found"}`
- Expired / no minutes: `{"valid":false,"reason":"expired","minutes_left":0}`

## Call End
- `POST /api/pbx/call-end`
- Sample body:
```json
{
  "token": "<card_uuid>",
  "call_id": "pbx-123",
  "duration_seconds": 125,
  "dialed_number": "2348012345678"
}
```
- Success: `{"ok":true,"billed_minutes":3,"minutes_left":42,"card_status":"active"}`
- Expired: `{"ok":false,"reason":"expired","minutes_left":0,"card_status":"expired"}`
- Not found: `{"ok":false,"reason":"not_found"}`

## Billing rule
- Billed minutes = `ceil(duration_seconds / 60)`, minimum 1 if `duration_seconds > 0`.
- Minutes are deducted per call; card auto-expires when minutes reach 0.

## Token rules
- Token = call card UUID (multi-use) until minutes are fully consumed.
- Validation blocks expired cards or those with zero remaining minutes.
