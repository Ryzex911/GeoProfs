import { expect, type Locator, type Page } from '@playwright/test';

async function clickCalendarDay(page: Page, day: number) {
    const btn = page.getByRole('button', { name: new RegExp(`^${day}$`) }).first();
    await expect(btn).toBeVisible();
    await btn.click();
}

async function getLeaveTypeSelect(page: Page): Promise<Locator> {
    // 1) Probeer label-based (als label correct gekoppeld is)
    const byLabel = page.getByLabel(/verloftype/i);
    if (await byLabel.isVisible().catch(() => false)) return byLabel;

    // 2) Probeer select met placeholder option "Kies een verloftype"
    const filtered = page
        .locator('select')
        .filter({ has: page.locator('option', { hasText: /kies een verloftype/i }) })
        .first();

    if (await filtered.isVisible().catch(() => false)) return filtered;

    // 3) Fallback: eerste select op de pagina
    return page.locator('select').first();
}

async function pickLeaveType(page: Page, label?: string) {
    const select = await getLeaveTypeSelect(page);
    await expect(select).toBeVisible();
    await expect(select).toBeEnabled();

    // Wacht tot options geladen zijn (meer dan placeholder)
    await expect.poll(async () => await select.locator('option').count(), { timeout: 25_000 }).toBeGreaterThan(1);

    // Lees option texts/values
    const options = await select.locator('option').evaluateAll((nodes) =>
        nodes.map((n) => ({
            text: (n.textContent ?? '').trim(),
            value: (n as HTMLOptionElement).value,
        })),
    );

    // Kies target option
    let targetIndex = 1; // default eerste echte optie
    if (label) {
        const idx = options.findIndex((o) => o.text.toLowerCase() === label.trim().toLowerCase());
        if (idx >= 0) targetIndex = idx;
    }

    const value = options[targetIndex]?.value;
    if (!value) throw new Error(`No selectable leave type found. Options: ${JSON.stringify(options)}`);

    // Zet value via JS + change event (voorkomt selectOption hang)
    await select.evaluate((el: HTMLSelectElement, v: string) => {
        el.value = v;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }, value);

    await expect.poll(async () => await select.inputValue()).toBe(value);
}

function vanInput(page: Page) {
    // input direct na label "Van"
    return page.getByText(/^Van$/i).locator('xpath=following::input[1]').first();
}

function totInput(page: Page) {
    // input direct na label "Tot"
    return page.getByText(/^Tot$/i).locator('xpath=following::input[1]').first();
}

export async function getDashboardPendingCount(page: Page): Promise<number> {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/dashboard/i);

    const text = await page.locator('body').innerText();
    const m = text.match(/Lopende aanvragen\s*[\r\n]+(\d+)/i);
    return m ? Number(m[1]) : 0;
}

export async function openLeaveRequestForm(page: Page) {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/dashboard/i);

    await page.getByRole('button', { name: /nieuw verlof verzoek/i }).click();
    await expect(page).toHaveURL(/requestdashboard/i);

    // kleine render-wacht
    await page.waitForTimeout(300);
}

export async function fillLeaveForm(page: Page, params: { typeLabel?: string; startDay: number; endDay: number; remark: string }) {
    // 1) Verloftype kiezen
    await pickLeaveType(page, params.typeLabel);

    // 2) Datums via kalender klikken (jouw UI)
    await clickCalendarDay(page, params.startDay);
    await clickCalendarDay(page, params.endDay);

    // 3) Opmerking invullen
    const remark = page.getByPlaceholder(/eventuele toelichting/i).or(page.locator('textarea').first());
    await expect(remark).toBeVisible();
    await remark.fill(params.remark);

    // 4) Bewijs: Van/Tot inputs moeten nu een waarde hebben (geen placeholder check)
    const from = vanInput(page);
    const to = totInput(page);

    await expect(from).toBeVisible();
    await expect(to).toBeVisible();

    await expect.poll(async () => await from.inputValue(), { timeout: 10_000 }).not.toBe('');
    await expect.poll(async () => await to.inputValue(), { timeout: 10_000 }).not.toBe('');
}

export async function submitLeaveForm(page: Page) {
    const btn = page.getByRole('button', { name: /verzoek indienen/i });

    // Soms is submit disabled tot state update klaar is
    await expect(btn).toBeVisible();
    await expect(btn).toBeEnabled({ timeout: 10_000 });

    await btn.click();
}

export async function cancelLeaveForm(page: Page) {
    await page.getByRole('button', { name: /^annuleren$/i }).click();
}
