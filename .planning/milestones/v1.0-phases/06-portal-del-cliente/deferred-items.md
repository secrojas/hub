# Deferred Items — Phase 06

## Pre-existing Test Failure (out of scope)

**File:** `tests/Feature/Billing/BillingDashboardTest.php`
**Test:** `test_cobrado_mes_excludes_other_months`
**Status:** Pre-existing failure — present before Plan 06-01 execution
**Root cause:** The first test in the class (`test_summary_shows_correct_cobrado_mes`) creates a `pagado` billing with `fecha_pago: now()`. When the second test runs and queries `cobrado_mes`, it picks up billings from the FIRST test because `RefreshDatabase` between tests within the same class may not fully isolate — or the BillingController's `cobrado_mes` query does not filter by month correctly for this edge case.
**Impact:** None on Portal. BillingController behavior unchanged.
**Action required:** Fix `BillingDashboardTest` to not rely on empty DB state in second test (use dedicated assertions or create only isolated data).
