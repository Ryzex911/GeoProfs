# US007: Test Plan voor Verlofsaldo Bekijken

## Overzicht
Dit testplan beschrijft de testgevallen voor de functionaliteit om het resterende verlofsaldo van een werknemer te bekijken. De testgevallen dekken zowel happy path als unhappy path scenario's, inclusief edge cases.

## Test Scope
- API Endpoint: GET /api/me/leave-balance
- User Model Method: getLeaveBalance()
- LeaveService Methods: getRemainingDays(), getUsedDays(), calculateWorkingHoursBetween()

## Testgevallen

### TC001: Happy Path - Gebruiker bekijkt eigen verlofsaldo (Authenticated User)
**Beschrijving:** Een ingelogde gebruiker vraagt zijn/haar verlofsaldo op via de API.
**Precondities:**
- Gebruiker is ingelogd
- Gebruiker heeft een startsaldo van 25 dagen (200 uur)
- Geen goedgekeurde verlofaanvragen

**Stappen:**
1. Gebruiker logt in
2. Gebruiker roept GET /api/me/leave-balance aan
3. Systeem retourneert JSON response

**Verwacht Resultaat:**
- HTTP Status: 200 OK
- Response bevat: remaining_days: 25.0, used_days: 0.0, start_days: 25.0
- Response structuur: {"remaining_days": 25.0, "used_days": 0.0, "start_days": 25.0}

**Test Type:** Integration Test
**Correspondeert met:** UserLeaveBalanceTest::test_get_leave_balance_returns_array_structure

### TC002: Happy Path - Gebruiker met gebruikt verlof bekijkt saldo
**Beschrijving:** Een ingelogde gebruiker met goedgekeurde verlofaanvragen bekijkt zijn/haar saldo.
**Precondities:**
- Gebruiker is ingelogd
- Gebruiker heeft 40 uur goedgekeurd verlof (5 dagen)

**Stappen:**
1. Gebruiker logt in
2. Gebruiker roept GET /api/me/leave-balance aan

**Verwacht Resultaat:**
- HTTP Status: 200 OK
- Response: remaining_days: 20.0, used_days: 5.0, start_days: 25.0

**Test Type:** Integration Test
**Correspondeert met:** LeaveServiceTest::test_get_remaining_hours_calculates_correctly

### TC003: Unhappy Path - Niet-geauthenticeerde gebruiker
**Beschrijving:** Een niet-ingelogde gebruiker probeert verlofsaldo op te vragen.
**Precondities:**
- Geen gebruiker ingelogd

**Stappen:**
1. Anonieme gebruiker roept GET /api/me/leave-balance aan

**Verwacht Resultaat:**
- HTTP Status: 401 Unauthorized
- Response: {"message": "Unauthenticated."}

**Test Type:** Security Test

### TC004: Edge Case - Negatief saldo (meer verlof gebruikt dan beschikbaar)
**Beschrijving:** Gebruiker heeft meer verlof gebruikt dan zijn/haar startsaldo.
**Precondities:**
- Gebruiker heeft 250 uur goedgekeurd verlof (meer dan 200 uur startsaldo)

**Stappen:**
1. Gebruiker logt in
2. Gebruiker roept GET /api/me/leave-balance aan

**Verwacht Resultaat:**
- HTTP Status: 200 OK
- Response: remaining_days: 0.0 (nooit negatief)
- used_days: 31.25, start_days: 25.0

**Test Type:** Edge Case Test
**Correspondeert met:** LeaveServiceTest::test_get_remaining_days_never_negative

### TC005: Edge Case - Verlof dat niet aftrekt van saldo
**Beschrijving:** Gebruiker heeft verlof van type dat niet aftrekt van saldo.
**Precondities:**
- Gebruiker heeft 24 uur goedgekeurd verlof van niet-aftrekkend type
- Gebruiker heeft 16 uur goedgekeurd verlof van aftrekkend type

**Stappen:**
1. Gebruiker logt in
2. Gebruiker roept GET /api/me/leave-balance aan

**Verwacht Resultaat:**
- HTTP Status: 200 OK
- Response: remaining_days: 23.0, used_days: 2.0, start_days: 25.0
- Niet-aftrekkend verlof (24 uur) wordt niet meegeteld in used_days

**Test Type:** Business Logic Test
**Correspondeert met:** LeaveServiceTest::test_get_used_hours_ignores_non_deducting_leave_types

### TC006: Edge Case - Pro-rata startsaldo
**Beschrijving:** Gebruiker met parttime contract (FTE < 1.0) bekijkt saldo.
**Precondities:**
- Gebruiker heeft FTE van 0.8
- Startdatum: 1 januari 2023

**Stappen:**
1. Gebruiker logt in
2. Gebruiker roept GET /api/me/leave-balance aan

**Verwacht Resultaat:**
- HTTP Status: 200 OK
- start_days: 20.0 (25 * 0.8)
- remaining_days: 20.0 (als geen gebruikt verlof)

**Test Type:** Business Logic Test

### TC007: Performance Test - Response tijd
**Beschrijving:** Controleer of API snel reageert.
**Precondities:**
- Gebruiker ingelogd

**Stappen:**
1. Gebruiker roept GET /api/me/leave-balance aan (meerdere keren)

**Verwacht Resultaat:**
- Response tijd < 400ms
- Consistente performance

**Test Type:** Performance Test

### TC008: Data Integrity - Correcte berekening werkuren
**Beschrijving:** Controleer berekening van werkuren tussen datums.
**Precondities:**
- Periode van 5 werkdagen (ma-vr)

**Stappen:**
1. Systeem berekent werkuren tussen 01-01-2023 (zo) en 07-01-2023 (za)

**Verwacht Resultaat:**
- Werkuren: 40.0 (5 dagen * 8 uur)
- Weekends uitgesloten

**Test Type:** Unit Test
**Correspondeert met:** LeaveServiceTest::test_calculate_working_hours_between_excludes_weekends

### TC009: Data Integrity - Uitsluiten feestdagen
**Beschrijving:** Controleer uitsluiten van feestdagen in werkuren berekening.
**Precondities:**
- Periode bevat feestdag

**Stappen:**
1. Systeem berekent werkuren tussen 01-01-2023 (zo) en 07-01-2023 (za)
2. Feestdag: 02-01-2023 (ma)

**Verwacht Resultaat:**
- Werkuren: 32.0 (4 dagen * 8 uur)
- Feestdag uitgesloten

**Test Type:** Unit Test
**Correspondeert met:** LeaveServiceTest::test_calculate_working_hours_between_excludes_holidays

### TC010: Integration Test - Workflow goedkeuring update saldo
**Beschrijving:** Controleer of goedkeuring van verlofaanvraag saldo update.
**Precondities:**
- Verlofaanvraag ingediend
- Manager keurt goed

**Stappen:**
1. Manager keurt verlofaanvraag goed (16 uur)
2. Gebruiker bekijkt saldo

**Verwacht Resultaat:**
- used_days verhoogd met 2.0
- remaining_days verlaagd met 2.0
- Transactie veilig (lockForUpdate)

**Test Type:** Integration Test

## Test Uitvoering
- **Test Environment:** Development/Lokaal
- **Test Data:** Gebruik factories voor test data
- **Tools:** PHPUnit voor unit tests, Postman voor API tests
- **Verantwoordelijk:** QA Team / Developer

## Acceptatie Criteria
- Alle testgevallen slagen
- Code coverage > 80% voor gerelateerde klassen
- Performance requirements gehaald
- Geen security vulnerabilities

## Risico's
- Concurrency issues bij gelijktijdige goedkeuringen
- Incorrecte berekening bij complexe scenario's
- Performance degradation bij veel data
