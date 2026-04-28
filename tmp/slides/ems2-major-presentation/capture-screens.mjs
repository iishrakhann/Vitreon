import fs from "node:fs/promises";
import path from "node:path";
import { chromium } from "playwright";

const edgePath = "C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe";
const shotDir = "C:\\xampp\\htdocs\\EMS2\\tmp\\slides\\ems2-major-presentation\\screenshots";

await fs.mkdir(shotDir, { recursive: true });

const browser = await chromium.launch({
  headless: true,
  executablePath: edgePath,
});

const page = await browser.newPage({
  viewport: { width: 1440, height: 960 },
  deviceScaleFactor: 1,
});

const targets = [
  ["01-home", "http://127.0.0.1:8010/"],
  ["02-venues", "http://127.0.0.1:8010/venues"],
  ["03-login", "http://127.0.0.1:8010/login"],
  ["04-register", "http://127.0.0.1:8010/register"],
  ["05-venue-detail", "http://127.0.0.1:8010/venues/grand-pavilion"],
];

for (const [name, url] of targets) {
  await page.goto(url, { waitUntil: "domcontentloaded", timeout: 30000 });
  await page.screenshot({
    path: path.join(shotDir, `${name}.png`),
    fullPage: true,
  });
}

await browser.close();
console.log(shotDir);
