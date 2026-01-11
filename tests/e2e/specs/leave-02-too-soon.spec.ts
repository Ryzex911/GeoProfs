import { test, expect } from "@playwright/test";
import { loginToDashboardWith2fa, maybePause } from "../helpers/auth2fa";
import { openLeaveRequestForm, fillLeaveForm, submitLeaveForm } from "../helpers/leaveUi";

test("Scenario 2 - Case 2: < 7 dagen vooruit -> alert (in beeld) en geen aanvraag", async ({ page }) => {
    test.setTimeout(120_000);

    // 1) login + 2FA
    await loginToDashboardWith2fa(page, process.env.E2E_EMAIL!, process.env.E2E_PASSWORD!);

    // 2) bewijs vóór: aantal aanvragen
    await page.goto("/leave-requests");
    const before = await page.locator("table tbody tr").count();

    // 3) naar aanvraagformulier
    await openLeaveRequestForm(page);

    // 4) te vroeg (< 7 dagen) + type + opmerking
    await fillLeaveForm(page, {
        typeLabel: process.env.E2E_LEAVE_TYPE_LABEL ?? "Vakantie",
        startDay: 12,
        endDay: 13,
        remark: `test invalid datum ${Date.now()}`,
    });

    // 5) zet listener klaar vóór submit (anders mis je de alert)
    const dialogPromise = page.waitForEvent("dialog");

    // 6) klik verzenden
    await submitLeaveForm(page);

    // 7) alert controleren + voor demo even laten staan
    const dialog = await dialogPromise;
    expect(dialog.type()).toBe("alert");
    expect(dialog.message()).toMatch(/minimaal\s*7\s*dagen|7\s*dagen/i);

    // DEMO: laat popup 2.5s in beeld
    if (process.env.E2E_DEMO_SLOW === "1") {
        await new Promise((r) => setTimeout(r, 2500));
    }

    await dialog.accept();

    // DEMO: nog 1s wachten na OK
    if (process.env.E2E_DEMO_SLOW === "1") {
        await new Promise((r) => setTimeout(r, 1000));
    }

    // 8) bewijs na: geen extra aanvraag
    await page.goto("/leave-requests");
    const after = await page.locator("table tbody tr").count();
    expect(after).toBe(before);

    await maybePause(page);
});
