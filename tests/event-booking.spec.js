const { test, expect } = require('@playwright/test');

test.describe('Event Management & Ticket Booking', () => {
  test('User registration and login', async ({ page }) => {
    // Register a new user
    await page.goto('http://localhost/index.php');
    await page.click('text=Register');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password123');
    await page.selectOption('#role', 'user');
    await page.click('button[type="submit"]');
    await expect(page.locator('#message')).toContainText('Registration successful');

    // Login
    await page.click('text=Login');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/index\.php/);
  });

  test('View events and book ticket', async ({ page }) => {
    // Assume logged in or login first
    await page.goto('http://localhost/login.php');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password123');
    await page.click('button[type="submit"]');

    // View events
    await expect(page.locator('#events-list')).toBeVisible();
    const eventCard = page.locator('.event-card').first();
    await expect(eventCard).toContainText('Title'); // Assuming events exist

    // Book ticket
    await page.click('text=Book Ticket');
    await page.fill('#quantity', '2');
    await page.click('button[type="submit"]');
    await expect(page.locator('#message')).toContainText('Booking created');
  });

  test('View and cancel booking', async ({ page }) => {
    // Login
    await page.goto('http://localhost/login.php');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password123');
    await page.click('button[type="submit"]');

    // Go to dashboard
    await page.click('text=Dashboard');
    await expect(page.locator('#bookings-list')).toBeVisible();

    // Cancel booking
    await page.click('text=Cancel');
    await expect(page.locator('#bookings-list')).not.toContainText('pending'); // Assuming status changes
  });
});