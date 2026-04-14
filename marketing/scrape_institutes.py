"""
IELTS Institute Scraper - Google Maps
=====================================
Scrapes IELTS coaching institutes from Google Maps across target cities.
Extracts: name, city, rating, reviews, phone, website, address.
Phase 2: visits each website to find email addresses.

Requirements:
    pip install playwright beautifulsoup4 requests
    playwright install chromium

Output: ielts_institutes.csv
"""

import asyncio
import csv
import random
import re
import sys
import time

# Fix Windows terminal encoding
sys.stdout.reconfigure(encoding='utf-8', errors='replace')
sys.stderr.reconfigure(encoding='utf-8', errors='replace')

import requests
from playwright.async_api import async_playwright

# ── Config ────────────────────────────────────────────────────────────────────

CITIES = [
    "Jalandhar",
    "Ludhiana",
    "Amritsar",
    "Ahmedabad",
    "Surat",
    "Kochi",
    "Delhi",
    "Hyderabad",
]

MAX_PER_CITY = 7
OUTPUT_FILE  = "ielts_institutes.csv"

# ── Phase 1: Collect place URLs from search results ───────────────────────────

async def get_place_urls(page, city):
    """Return up to MAX_PER_CITY unique /maps/place/ URLs for a city search."""
    query = f"IELTS coaching {city}"
    url   = f"https://www.google.com/maps/search/{query.replace(' ', '+')}"

    print(f"\n[{city}] Searching ...")
    await page.goto(url, wait_until="domcontentloaded", timeout=60000)
    await page.wait_for_timeout(3500)

    # Scroll sidebar to load more results
    sidebar = await page.query_selector('[role="feed"]')
    if sidebar:
        for _ in range(5):
            await sidebar.evaluate("el => el.scrollTop += 600")
            await page.wait_for_timeout(700)

    # Grab all place links
    anchors = await page.query_selector_all('[role="feed"] a[href*="/maps/place/"]')
    urls, seen = [], set()
    for a in anchors:
        href = (await a.get_attribute("href") or "").split("?")[0]
        if href and href not in seen:
            seen.add(href)
            urls.append(href)
        if len(urls) >= MAX_PER_CITY:
            break

    print(f"[{city}] {len(urls)} place URLs collected")
    return urls


# ── Phase 1: Extract details from each place page ────────────────────────────

async def extract_place(page, place_url, city):
    """Navigate directly to a place URL and extract its details."""
    await page.goto(place_url, wait_until="domcontentloaded", timeout=60000)
    await page.wait_for_timeout(2500)

    data = {"city": city, "name": "", "rating": "", "reviews": "",
            "phone": "", "website": "", "address": ""}

    # Name — wait until h1 is NOT empty and NOT "Results"
    for _ in range(10):
        el = await page.query_selector("h1")
        if el:
            txt = (await el.inner_text()).strip()
            if txt and txt.lower() not in ("results", ""):
                data["name"] = txt
                break
        await page.wait_for_timeout(500)

    if not data["name"]:
        return data   # couldn't load

    # Rating
    for sel in ['span[aria-label*="star"]', '[jsaction*="pane.rating"] span']:
        el = await page.query_selector(sel)
        if el:
            label = await el.get_attribute("aria-label") or await el.inner_text()
            m = re.search(r"([\d.]+)\s*star", label, re.I)
            if m:
                data["rating"] = m.group(1)
            m2 = re.search(r"([\d,]+)\s*review", label, re.I)
            if m2:
                data["reviews"] = m2.group(1).replace(",", "")
            break

    # Phone
    for sel in [
        'button[data-tooltip*="phone" i]',
        'button[aria-label*="phone" i]',
        '[data-item-id*="phone"] span',
        'span[aria-label*="phone" i]',
    ]:
        el = await page.query_selector(sel)
        if el:
            txt = (await el.inner_text()).strip()
            if re.search(r'[\d\s\-\+\(\)]{7,}', txt):
                data["phone"] = txt
                break

    # Website
    for sel in [
        'a[data-tooltip="Open website"]',
        'a[data-item-id="authority"]',
        'a[aria-label*="website" i]',
    ]:
        el = await page.query_selector(sel)
        if el:
            href = (await el.get_attribute("href") or "").strip()
            if href.startswith("http"):
                data["website"] = href
                break

    # Address
    for sel in [
        'button[data-tooltip="Copy address"]',
        'button[aria-label*="address" i]',
        '[data-item-id*="address"] span',
    ]:
        el = await page.query_selector(sel)
        if el:
            txt = (await el.inner_text()).strip()
            if txt:
                data["address"] = txt
                break

    return data


async def scrape_city(page, city):
    """Scrape one city: collect URLs then extract each place."""
    place_urls = await get_place_urls(page, city)
    results    = []

    for i, purl in enumerate(place_urls, 1):
        try:
            data = await extract_place(page, purl, city)
            if data["name"]:
                results.append(data)
                print(f"  [{i}] {data['name'][:45]:<45} | {data['phone'] or 'no phone'}")
            else:
                print(f"  [{i}] (could not load place)")
        except Exception as exc:
            print(f"  [{i}] Error: {exc}")

        await asyncio.sleep(random.uniform(1.2, 2.2))

    return results


async def phase1_scrape():
    all_results = []

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=False)
        context = await browser.new_context(
            user_agent=(
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) "
                "Chrome/124.0.0.0 Safari/537.36"
            ),
            viewport={"width": 1280, "height": 800},
        )
        page = await context.new_page()

        for city in CITIES:
            city_results = await scrape_city(page, city)
            all_results.extend(city_results)
            await asyncio.sleep(random.uniform(2, 4))

        await browser.close()

    return all_results


# ── Phase 2: Email finder ─────────────────────────────────────────────────────

EMAIL_RE = re.compile(r"[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}")

def find_emails_on_page(url):
    if not url or not url.startswith("http"):
        return ""
    try:
        resp = requests.get(url, timeout=8, headers={
            "User-Agent": "Mozilla/5.0 (compatible; outreach-bot/1.0)"
        })
        emails = set(EMAIL_RE.findall(resp.text))

        try:
            r2 = requests.get(url.rstrip("/") + "/contact", timeout=6,
                              headers={"User-Agent": "Mozilla/5.0"})
            emails.update(EMAIL_RE.findall(r2.text))
        except Exception:
            pass

        bad = {"png", "jpg", "jpeg", "gif", "svg", "css", "js", "woff"}
        emails = {
            e for e in emails
            if not any(e.lower().endswith(f".{b}") for b in bad)
            and "example" not in e and "domain" not in e
        }
        return ", ".join(sorted(emails))
    except Exception:
        return ""


def phase2_emails(institutes):
    print("\n\n-- Phase 2: Finding emails --------------------------")
    for idx, inst in enumerate(institutes, 1):
        website = inst.get("website", "")
        if website:
            print(f"  [{idx}/{len(institutes)}] {inst['name'][:40]} -> {website[:50]}")
            inst["email"] = find_emails_on_page(website)
            if inst["email"]:
                print(f"        found: {inst['email']}")
            time.sleep(random.uniform(1, 2))
        else:
            inst["email"] = ""
    return institutes


# ── Save ──────────────────────────────────────────────────────────────────────

FIELDS = ["name", "city", "rating", "reviews", "phone", "email", "website", "address"]

def save_csv(institutes, path=OUTPUT_FILE):
    with open(path, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=FIELDS, extrasaction="ignore")
        writer.writeheader()
        writer.writerows(institutes)
    print(f"\nSaved {len(institutes)} institutes -> {path}")


# ── Main ──────────────────────────────────────────────────────────────────────

async def main():
    print("=" * 60)
    print("  IELTS Institute Scraper")
    print("=" * 60)

    institutes = await phase1_scrape()
    print(f"\nPhase 1 complete - {len(institutes)} institutes collected")

    save_csv(institutes, "ielts_institutes_noemail.csv")

    institutes = phase2_emails(institutes)
    save_csv(institutes, OUTPUT_FILE)

    with_phone   = sum(1 for i in institutes if i.get("phone"))
    with_email   = sum(1 for i in institutes if i.get("email"))
    with_website = sum(1 for i in institutes if i.get("website"))
    print(f"\nSummary:")
    print(f"  Total      : {len(institutes)}")
    print(f"  Has phone  : {with_phone}")
    print(f"  Has email  : {with_email}")
    print(f"  Has website: {with_website}")
    print(f"\nOpen {OUTPUT_FILE} in Excel / Google Sheets.")


if __name__ == "__main__":
    asyncio.run(main())
