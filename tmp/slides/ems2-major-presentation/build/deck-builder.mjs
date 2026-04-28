const fs = await import("node:fs/promises");
const path = await import("node:path");
const { Presentation, PresentationFile } = await import("@oai/artifact-tool");

const W = 1280;
const H = 720;

const DECK_ID = "ems2-major-presentation";
const OUT_DIR = "C:\\xampp\\htdocs\\EMS2\\outputs\\ems2-major-presentation";
const SCRATCH_DIR = path.resolve("C:\\xampp\\htdocs\\EMS2\\tmp\\slides\\ems2-major-presentation");
const PREVIEW_DIR = path.join(SCRATCH_DIR, "preview");
const VERIFICATION_DIR = path.join(SCRATCH_DIR, "verification");
const INSPECT_PATH = path.join(SCRATCH_DIR, "inspect.ndjson");
const MAX_RENDER_VERIFY_LOOPS = 3;

const BG = "#F7F4EE";
const SURFACE = "#FFFDFC";
const SURFACE_ALT = "#EFE7DA";
const TEXT = "#1E2933";
const MUTED = "#5F6C76";
const LINE = "#D9D1C4";
const TEAL = "#16867F";
const TEAL_DARK = "#0F5C57";
const GOLD = "#C78A2C";
const CORAL = "#D96B4D";
const NAVY = "#2C4E67";
const WHITE = "#FFFFFF";
const TRANSPARENT = "#00000000";

const TITLE_FACE = "Aptos";
const BODY_FACE = "Aptos";
const MONO_FACE = "Aptos Mono";

const LOGO_PATH = "C:\\xampp\\htdocs\\EMS2\\php-app\\public\\assets\\puneeventhub-logo.jpg";

const SOURCES = {
  readme: "README.md",
  detailed: "documentation/detailed-project-report.md",
  full: "documentation/full-project-report.md",
  script: "documentation/project-explanation-script.md",
  views: "php-app/src/Views/*.php",
};

const inspectRecords = [];

async function pathExists(target) {
  try {
    await fs.access(target);
    return true;
  } catch {
    return false;
  }
}

async function ensureDirs() {
  await fs.mkdir(OUT_DIR, { recursive: true });
  await fs.mkdir(SCRATCH_DIR, { recursive: true });
  await fs.mkdir(PREVIEW_DIR, { recursive: true });
  await fs.mkdir(VERIFICATION_DIR, { recursive: true });
}

function lineConfig(fill = TRANSPARENT, width = 0) {
  return { style: "solid", fill, width };
}

function normalizeText(text) {
  if (Array.isArray(text)) {
    return text.join("\n");
  }
  return String(text ?? "");
}

function textLineCount(text) {
  const value = normalizeText(text);
  return value.trim() ? value.split(/\n/).length : 0;
}

function requiredTextHeight(text, fontSize, lineHeight = 1.16) {
  return Math.max(10, Math.max(1, textLineCount(text)) * fontSize * lineHeight);
}

function assertTextFits(text, h, size, role) {
  const needed = requiredTextHeight(text, size);
  const tolerance = Math.max(3, size * 0.12);
  if (normalizeText(text).trim() && h + tolerance < needed) {
    throw new Error(`${role} overflow: height=${h}, needed=${needed}, text=${JSON.stringify(normalizeText(text).slice(0, 90))}`);
  }
}

function recordShape(slideNo, shape, role, kind, x, y, w, h) {
  inspectRecords.push({
    kind: "shape",
    slide: slideNo,
    id: shape?.id || `${slideNo}-${role}-${inspectRecords.length + 1}`,
    role,
    shapeType: kind,
    bbox: [x, y, w, h],
  });
}

function recordText(slideNo, shape, role, text, x, y, w, h) {
  const value = normalizeText(text);
  inspectRecords.push({
    kind: "textbox",
    slide: slideNo,
    id: shape?.id || `${slideNo}-${role}-${inspectRecords.length + 1}`,
    role,
    text: value,
    textChars: value.length,
    textLines: textLineCount(value),
    bbox: [x, y, w, h],
  });
}

function recordImage(slideNo, image, role, imagePath, x, y, w, h) {
  inspectRecords.push({
    kind: "image",
    slide: slideNo,
    id: image?.id || `${slideNo}-${role}-${inspectRecords.length + 1}`,
    role,
    path: imagePath,
    bbox: [x, y, w, h],
  });
}

function addShape(slide, slideNo, geometry, x, y, w, h, fill, line = TRANSPARENT, lineWidth = 0, role = geometry) {
  const shape = slide.shapes.add({
    geometry,
    position: { left: x, top: y, width: w, height: h },
    fill,
    line: lineConfig(line, lineWidth),
  });
  recordShape(slideNo, shape, role, geometry, x, y, w, h);
  return shape;
}

function addText(
  slide,
  slideNo,
  text,
  x,
  y,
  w,
  h,
  {
    size = 22,
    color = TEXT,
    bold = false,
    face = BODY_FACE,
    align = "left",
    valign = "top",
    fill = TRANSPARENT,
    line = TRANSPARENT,
    lineWidth = 0,
    role = "text",
    checkFit = true,
  } = {},
) {
  if (checkFit) {
    assertTextFits(text, h, size, role);
  }
  const box = addShape(slide, slideNo, "rect", x, y, w, h, fill, line, lineWidth, role);
  box.text = text;
  box.text.fontSize = size;
  box.text.color = color;
  box.text.bold = bold;
  box.text.typeface = face;
  box.text.alignment = align;
  box.text.verticalAlignment = valign;
  box.text.insets = { left: 0, right: 0, top: 0, bottom: 0 };
  recordText(slideNo, box, role, text, x, y, w, h);
  return box;
}

async function readImageBlob(imagePath) {
  const bytes = await fs.readFile(imagePath);
  return bytes.buffer.slice(bytes.byteOffset, bytes.byteOffset + bytes.byteLength);
}

async function addImage(slide, slideNo, imagePath, x, y, w, h, role = "image", fit = "contain") {
  const image = slide.images.add({
    blob: await readImageBlob(imagePath),
    fit,
    alt: role,
  });
  image.position = { left: x, top: y, width: w, height: h };
  recordImage(slideNo, image, role, imagePath, x, y, w, h);
  return image;
}

function addHeader(slide, slideNo, section, page) {
  addShape(slide, slideNo, "rect", 0, 0, W, H, BG, TRANSPARENT, 0, "background");
  addShape(slide, slideNo, "rect", 996, 0, 284, H, "#ECE5D8", TRANSPARENT, 0, "side rail");
  addShape(slide, slideNo, "rect", 968, 0, 12, H, TEAL, TRANSPARENT, 0, "side accent");
  addShape(slide, slideNo, "rect", 0, 0, 178, 12, TEAL, TRANSPARENT, 0, "top accent");
  addText(slide, slideNo, section.toUpperCase(), 70, 38, 320, 20, {
    size: 13,
    color: TEAL_DARK,
    bold: true,
    face: MONO_FACE,
    role: "section label",
    checkFit: false,
  });
  addText(slide, slideNo, page, 1128, 38, 80, 20, {
    size: 13,
    color: TEAL_DARK,
    bold: true,
    face: MONO_FACE,
    align: "right",
    role: "page label",
    checkFit: false,
  });
  addShape(slide, slideNo, "rect", 70, 66, 1140, 2, LINE, TRANSPARENT, 0, "header rule");
}

function addFooter(slide, slideNo) {
  addShape(slide, slideNo, "rect", 0, 680, W, 40, "#F1EBE1", TRANSPARENT, 0, "footer band");
  addText(slide, slideNo, "EMS2 | Major Project Presentation", 70, 693, 320, 14, {
    size: 11,
    color: MUTED,
    role: "footer",
    checkFit: false,
  });
}

function addTitle(slide, slideNo, title, subtitle, width = 860) {
  addText(slide, slideNo, title, 70, 98, width, 78, {
    size: 28,
    color: TEXT,
    bold: true,
    face: TITLE_FACE,
    role: "title",
  });
  if (subtitle) {
    addText(slide, slideNo, subtitle, 70, 184, Math.min(width, 860), 52, {
      size: 17,
      color: MUTED,
      role: "subtitle",
    });
  }
}

function addPanel(slide, slideNo, x, y, w, h, accent = TEAL, role = "panel", fill = SURFACE) {
  addShape(slide, slideNo, "roundRect", x, y, w, h, fill, LINE, 1, `${role} panel`);
  addShape(slide, slideNo, "rect", x, y, 8, h, accent, TRANSPARENT, 0, `${role} accent`);
}

function addCard(slide, slideNo, x, y, w, h, heading, body, accent = TEAL, bodySize = 17) {
  addPanel(slide, slideNo, x, y, w, h, accent, heading);
  addText(slide, slideNo, heading, x + 24, y + 20, w - 40, 22, {
    size: 14,
    color: accent,
    bold: true,
    face: MONO_FACE,
    role: "card heading",
    checkFit: false,
  });
  addText(slide, slideNo, body, x + 24, y + 56, w - 46, h - 80, {
    size: bodySize,
    color: TEXT,
    role: "card body",
  });
}

function addMetric(slide, slideNo, x, y, w, h, value, label, note, accent) {
  addPanel(slide, slideNo, x, y, w, h, accent, label);
  addText(slide, slideNo, value, x + 22, y + 18, w - 44, 36, {
    size: 30,
    color: TEXT,
    bold: true,
    face: TITLE_FACE,
    role: "metric value",
  });
  addText(slide, slideNo, label, x + 22, y + 62, w - 44, 20, {
    size: 15,
    color: MUTED,
    role: "metric label",
    checkFit: false,
  });
  addText(slide, slideNo, note, x + 22, y + 94, w - 44, 30, {
    size: 10,
    color: MUTED,
    role: "metric note",
  });
}

function addBullets(slide, slideNo, items, x, y, w, size = 18, gap = 38, color = TEXT) {
  items.forEach((item, index) => {
    const top = y + index * gap;
    addShape(slide, slideNo, "ellipse", x, top + 8, 10, 10, TEAL, TRANSPARENT, 0, "bullet");
    addText(slide, slideNo, item, x + 24, top, w - 24, 28, {
      size,
      color,
      role: "bullet text",
    });
  });
}

function addArrow(slide, slideNo, x, y, w, accent = TEAL) {
  addShape(slide, slideNo, "rect", x, y, w, 4, accent, TRANSPARENT, 0, "arrow line");
  addShape(slide, slideNo, "rightArrow", x + w - 20, y - 8, 20, 20, accent, TRANSPARENT, 0, "arrow head");
}

function addNode(slide, slideNo, x, y, w, h, title, lines, accent = TEAL, fill = SURFACE) {
  addPanel(slide, slideNo, x, y, w, h, accent, title, fill);
  addText(slide, slideNo, title, x + 24, y + 20, w - 48, 22, {
    size: 15,
    color: accent,
    bold: true,
    face: MONO_FACE,
    role: "node title",
    checkFit: false,
  });
  addBullets(slide, slideNo, lines, x + 24, y + 56, w - 48, 15, 28, TEXT);
}

function baseSlide(presentation, slideNo, section, page, title, subtitle, width = 860) {
  const slide = presentation.slides.add();
  slide.background.fill = BG;
  addHeader(slide, slideNo, section, page);
  addFooter(slide, slideNo);
  addTitle(slide, slideNo, title, subtitle, width);
  return slide;
}

async function slide1(presentation) {
  const slideNo = 1;
  const slide = presentation.slides.add();
  slide.background.fill = BG;
  addShape(slide, slideNo, "rect", 0, 0, W, H, BG, TRANSPARENT, 0, "background");
  addShape(slide, slideNo, "rect", 852, 0, 428, H, "#E9DFCF", TRANSPARENT, 0, "hero rail");
  addShape(slide, slideNo, "rect", 824, 0, 14, H, TEAL, TRANSPARENT, 0, "hero divider");
  if (await pathExists(LOGO_PATH)) {
    await addImage(slide, slideNo, LOGO_PATH, 904, 78, 212, 212, "logo");
  }
  addShape(slide, slideNo, "roundRect", 74, 88, 194, 34, SURFACE, TEAL, 1, "badge");
  addText(slide, slideNo, "MAJOR PROJECT PPT", 102, 97, 138, 16, {
    size: 13,
    color: TEAL_DARK,
    bold: true,
    face: MONO_FACE,
    align: "center",
    role: "badge text",
    checkFit: false,
  });
  addText(slide, slideNo, "EMS2", 74, 158, 320, 56, {
    size: 42,
    color: TEXT,
    bold: true,
    face: TITLE_FACE,
    role: "cover short title",
  });
  addText(slide, slideNo, "Event and Venue Management System", 74, 220, 640, 68, {
    size: 28,
    color: NAVY,
    bold: true,
    face: TITLE_FACE,
    role: "cover title",
  });
  addText(
    slide,
    slideNo,
    "A web-based platform for venue discovery, OTP authentication, booking workflow management, role-based dashboards, and future-ready integration support.",
    74,
    314,
    622,
    88,
    { size: 19, color: MUTED, role: "cover subtitle" },
  );
  addMetric(slide, slideNo, 74, 462, 190, 132, "17", "Slides", "Detailed academic presentation", TEAL);
  addMetric(slide, slideNo, 286, 462, 190, 132, "3", "User roles", "Customer, Owner, Administrator", GOLD);
  addMetric(slide, slideNo, 498, 462, 190, 132, "4", "Core layers", "PHP, MySQL, Java, Python", CORAL);
  addCard(slide, slideNo, 900, 334, 280, 238, "Presentation flow", "Introduction, existing system, scope, environment, technology, screens, and diagrams are organized in chapter order for major project delivery.", NAVY, 18);
  addFooter(slide, slideNo);
  slide.speakerNotes.setText("Introduce the project name, explain that this deck follows the major project chapter sequence, and mention the modular technology stack.");
}

function slide2(presentation) {
  const slideNo = 2;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Introduction",
    "02 / 17",
    "Introduction",
    "EMS2 digitizes the venue selection and booking process through a centralized web platform.",
  );
  addCard(slide, slideNo, 70, 258, 412, 312, "Project overview", "Customers can browse venues, compare price and capacity, authenticate with OTP, and move into a structured booking workflow from a single website.", TEAL);
  addCard(slide, slideNo, 514, 258, 412, 312, "Why it matters", "The same platform helps owners manage venues and bookings while administrators supervise users, status, and overall platform activity.", GOLD);
  addShape(slide, slideNo, "roundRect", 70, 596, 856, 44, SURFACE_ALT, TRANSPARENT, 0, "intro band");
  addText(slide, slideNo, "The project is designed as more than a listing site: it is an integrated event venue management workflow.", 98, 608, 810, 18, {
    size: 17,
    color: NAVY,
    role: "intro band text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Summarize the system at a high level and position it as a complete event and venue management project rather than a simple directory.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.full);
}

function slide3(presentation) {
  const slideNo = 3;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Existing System",
    "03 / 17",
    "Existing System",
    "Traditional venue booking often depends on phone calls, direct visits, notebooks, and disconnected records.",
  );
  addCard(slide, slideNo, 70, 258, 260, 280, "Manual process", "Customers must contact multiple venue providers separately to compare pricing, category, and date availability.", TEAL);
  addCard(slide, slideNo, 354, 258, 260, 280, "Record issues", "Owners may rely on notebooks or spreadsheets, which makes real-time status visibility weak and hard to coordinate.", GOLD);
  addCard(slide, slideNo, 638, 258, 260, 280, "Operational risk", "Since information is fragmented, duplicate promises and booking conflicts can happen when multiple requests arrive together.", CORAL);
  addBullets(
    slide,
    slideNo,
    [
      "Time-consuming for customers and owners",
      "No single platform for comparison and progress tracking",
      "Poor transparency in booking status and review visibility",
    ],
    98,
    566,
    790,
    17,
    32,
  );
  slide.speakerNotes.setText("Describe the current manual environment first so the audience clearly understands the problem space.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.script);
}

function slide4(presentation) {
  const slideNo = 4;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Need for System",
    "04 / 17",
    "Need for the Proposed System",
    "A web-based event management platform is needed to replace the fragmented and error-prone manual workflow.",
  );
  addMetric(slide, slideNo, 70, 256, 250, 136, "OTP", "Authentication layer", "Users can access booking actions through a controlled login and verification flow.", TEAL);
  addMetric(slide, slideNo, 346, 256, 250, 136, "HOLD", "Slot control", "Availability checks and temporary holds reduce overlapping booking requests.", GOLD);
  addMetric(slide, slideNo, 622, 256, 250, 136, "DB", "Centralized records", "Users, venues, bookings, reviews, and events are stored consistently.", CORAL);
  addPanel(slide, slideNo, 70, 432, 802, 188, NAVY, "need");
  addBullets(
    slide,
    slideNo,
    [
      "Customers need faster venue discovery and better visibility into price, category, and status.",
      "Owners need organized tools for listing management, slot updates, and booking review.",
      "Administrators need role-based monitoring, user control, and system-wide operational oversight.",
      "The project should remain extensible for AI recommendations and stronger payment automation.",
    ],
    98,
    468,
    756,
    17,
    34,
  );
  slide.speakerNotes.setText("Explain the proposed system as a direct response to the pain points shown on the previous slide.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.full);
}

function slide5(presentation) {
  const slideNo = 5;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Scope of Work",
    "05 / 17",
    "Scope of Work",
    "The project scope covers functional platform behavior, technical architecture, and academic software engineering deliverables.",
  );
  addCard(slide, slideNo, 70, 252, 250, 300, "Functional scope", "Browse venues, show details, register and log in with OTP, initiate bookings, track status, submit reviews, and manage dashboards.", TEAL);
  addCard(slide, slideNo, 344, 252, 250, 300, "Technical scope", "Use PHP, MySQL, HTML, CSS, and JavaScript in a modular controller-service-repository structure with external integration support.", GOLD);
  addCard(slide, slideNo, 618, 252, 250, 300, "Academic scope", "Demonstrate requirement analysis, UML and flow diagrams, database design, implementation, testing, documentation, and user manuals.", CORAL);
  addShape(slide, slideNo, "roundRect", 70, 580, 798, 52, SURFACE_ALT, TRANSPARENT, 0, "scope note");
  addText(slide, slideNo, "Current limitations keep the focus on venue booking rather than full end-to-end event execution management.", 96, 596, 754, 18, {
    size: 16,
    color: NAVY,
    role: "scope note text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Clarify that scope includes both working software features and the academic design and documentation expected from a major project.\n\n[Sources]\n- " + SOURCES.detailed);
}

function slide6(presentation) {
  const slideNo = 6;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Operating Environment",
    "06 / 17",
    "Operating Environment - Hardware",
    "The project runs in a standard web application setup and does not require specialized hardware.",
  );
  addMetric(slide, slideNo, 70, 256, 200, 130, "i3+", "Processor", "Intel Core i3, i5, or higher", TEAL);
  addMetric(slide, slideNo, 292, 256, 200, 130, "4 GB", "Minimum RAM", "8 GB is recommended for smoother use", GOLD);
  addMetric(slide, slideNo, 514, 256, 200, 130, "20 GB", "Storage", "Free disk space for code, DB, and services", CORAL);
  addMetric(slide, slideNo, 736, 256, 200, 130, "Net", "Internet", "Needed for payment, mail, or SMS integrations", NAVY);
  addPanel(slide, slideNo, 70, 430, 866, 188, TEAL, "hardware");
  addBullets(
    slide,
    slideNo,
    [
      "Standard monitor or laptop screen is sufficient for development and demonstration.",
      "Keyboard and mouse support routine use, testing, and operator tasks.",
      "The environment is practical for both local academic deployment and project demo setup.",
    ],
    98,
    468,
    820,
    17,
    38,
  );
  slide.speakerNotes.setText("Present hardware needs briefly and keep the focus on the fact that this is deployable in a normal academic lab or laptop environment.\n\n[Sources]\n- " + SOURCES.detailed);
}

function slide7(presentation) {
  const slideNo = 7;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Operating Environment",
    "07 / 17",
    "Operating Environment - Software",
    "EMS2 is developed in a common Windows web-stack environment with optional service integrations.",
  );
  addCard(slide, slideNo, 70, 252, 250, 304, "Core software", "Windows 10 or 11, XAMPP, Apache, MySQL, PHP, and a browser such as Chrome, Edge, or Firefox.", TEAL);
  addCard(slide, slideNo, 344, 252, 250, 304, "Development tools", "VS Code or a similar IDE is used for coding, while Git supports version control and project tracking.", GOLD);
  addCard(slide, slideNo, 618, 252, 250, 304, "Optional services", "SMTP or PHPMailer, Razorpay-style payment support, Twilio, Python runtime, and Java runtime with Maven.", CORAL);
  addShape(slide, slideNo, "roundRect", 70, 584, 798, 44, SURFACE_ALT, TRANSPARENT, 0, "software note");
  addText(slide, slideNo, "This environment keeps installation and testing accessible while still supporting multi-module architecture.", 96, 598, 756, 16, {
    size: 16,
    color: NAVY,
    role: "software note text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Use this slide to show that the software environment is practical, familiar, and well-suited for a major project implementation.\n\n[Sources]\n- " + SOURCES.detailed);
}

function slide8(presentation) {
  const slideNo = 8;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Technology Used",
    "08 / 17",
    "Detailed Description of the Technology Used",
    "The core stack combines backend logic, persistent storage, and front-end structure for a web-based booking platform.",
  );
  addCard(slide, slideNo, 70, 252, 250, 298, "PHP", "Handles routing, session management, controller flow, form processing, and dynamic page rendering for the main web application.", TEAL);
  addCard(slide, slideNo, 344, 252, 250, 298, "MySQL", "Stores users, venues, slots, bookings, reviews, and webhook-related records in a structured relational database.", GOLD);
  addCard(slide, slideNo, 618, 252, 250, 298, "HTML / CSS / JS", "Define page structure, styling, responsiveness, and client-side interaction across the public site and dashboard views.", CORAL);
  addShape(slide, slideNo, "roundRect", 70, 578, 798, 48, SURFACE_ALT, TRANSPARENT, 0, "tech summary");
  addText(slide, slideNo, "Together, these technologies form the working foundation of the EMS2 website and dashboard experience.", 96, 592, 754, 18, {
    size: 16,
    color: NAVY,
    role: "tech summary text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Cover the main stack first: backend logic, data storage, and front-end technologies.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.readme);
}

function slide9(presentation) {
  const slideNo = 9;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Technology Used",
    "09 / 17",
    "Supporting Technologies and Services",
    "The architecture also includes local server tooling, communication services, and auxiliary modules for future growth.",
  );
  addCard(slide, slideNo, 70, 252, 250, 304, "Apache and XAMPP", "Provide the local execution environment for PHP and MySQL, making academic setup and browser testing straightforward.", TEAL);
  addCard(slide, slideNo, 344, 252, 250, 304, "PHPMailer / payment / SMS", "Support OTP delivery, booking-related payment flow, webhook handling, and owner notification capabilities.", GOLD);
  addCard(slide, slideNo, 618, 252, 250, 304, "Python and Java modules", "Extend the project with review sentiment scoring and concurrency-aware booking validation for future-ready architecture.", CORAL);
  addBullets(
    slide,
    slideNo,
    [
      "These modules make the system more serious than a basic CRUD demo.",
      "They also create a clean path for future enhancements without redesigning the core platform.",
    ],
    96,
    584,
    760,
    16,
    28,
  );
  slide.speakerNotes.setText("Highlight that the project includes integration-ready and extensible components beyond the core PHP site.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.readme);
}

async function slide10(presentation) {
  const slideNo = 10;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Screens",
    "10 / 17",
    "Application Screens - Home and Venue Directory",
    "These layouts are based on the actual view structure defined in the EMS2 public-facing pages.",
  );
  addPanel(slide, slideNo, 70, 248, 392, 350, TEAL, "home screen");
  addText(slide, slideNo, "Home Page", 96, 268, 160, 20, {
    size: 15,
    color: TEAL_DARK,
    bold: true,
    face: MONO_FACE,
    role: "screen title",
    checkFit: false,
  });
  addShape(slide, slideNo, "roundRect", 96, 304, 340, 112, "#F8FBFC", LINE, 1, "hero block");
  addText(slide, slideNo, "Discover Pune venues with live availability and cleaner booking flow", 118, 330, 294, 50, {
    size: 18,
    color: TEXT,
    bold: true,
    role: "hero text",
  });
  addShape(slide, slideNo, "roundRect", 96, 438, 104, 26, "#E6F5F3", TEAL, 1, "button 1");
  addShape(slide, slideNo, "roundRect", 212, 438, 126, 26, "#FFF4E7", GOLD, 1, "button 2");
  addShape(slide, slideNo, "roundRect", 96, 486, 100, 80, SURFACE_ALT, TRANSPARENT, 0, "mini card 1");
  addShape(slide, slideNo, "roundRect", 208, 486, 100, 80, SURFACE_ALT, TRANSPARENT, 0, "mini card 2");
  addShape(slide, slideNo, "roundRect", 320, 486, 100, 80, SURFACE_ALT, TRANSPARENT, 0, "mini card 3");

  addPanel(slide, slideNo, 492, 248, 392, 350, GOLD, "venues screen");
  addText(slide, slideNo, "Venue Listing Page", 518, 268, 180, 20, {
    size: 15,
    color: GOLD,
    bold: true,
    face: MONO_FACE,
    role: "screen title",
    checkFit: false,
  });
  for (let i = 0; i < 3; i += 1) {
    const x = 518 + i * 118;
    addShape(slide, slideNo, "roundRect", x, 308, 104, 210, SURFACE, LINE, 1, `venue card ${i + 1}`);
    addShape(slide, slideNo, "rect", x + 12, 320, 80, 66, "#E9E3D9", TRANSPARENT, 0, `venue image ${i + 1}`);
    addText(slide, slideNo, `Venue ${i + 1}`, x + 12, 400, 80, 18, {
      size: 11,
      color: TEAL_DARK,
      bold: true,
      role: "venue card title",
      checkFit: false,
    });
    addText(slide, slideNo, "Category\nPrice\nCapacity", x + 12, 426, 72, 52, {
      size: 10,
      color: MUTED,
      role: "venue card meta",
    });
  }
  if (await pathExists(LOGO_PATH)) {
    await addImage(slide, slideNo, LOGO_PATH, 1022, 260, 166, 166, "logo small");
  }
  addCard(slide, slideNo, 1006, 452, 192, 146, "UI notes", "The public site emphasizes venue discovery, featured sections, and quick access to detailed venue pages.", NAVY, 16);
  slide.speakerNotes.setText("Explain that the screen section is derived from the current home and venues view files in the PHP application.\n\n[Sources]\n- " + SOURCES.views);
}

async function slide11(presentation) {
  const slideNo = 11;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Screens",
    "11 / 17",
    "Application Screens - Venue Detail, Auth, and Dashboard",
    "The system includes detailed venue interaction, OTP-based authentication, and role-sensitive dashboards.",
  );
  addPanel(slide, slideNo, 70, 248, 392, 350, CORAL, "detail screen");
  addText(slide, slideNo, "Venue Detail Page", 96, 268, 180, 20, {
    size: 15,
    color: CORAL,
    bold: true,
    face: MONO_FACE,
    role: "screen title",
    checkFit: false,
  });
  addShape(slide, slideNo, "rect", 96, 304, 340, 112, "#E9E3D9", TRANSPARENT, 0, "gallery area");
  addShape(slide, slideNo, "roundRect", 96, 430, 104, 74, SURFACE_ALT, TRANSPARENT, 0, "detail metric 1");
  addShape(slide, slideNo, "roundRect", 212, 430, 104, 74, SURFACE_ALT, TRANSPARENT, 0, "detail metric 2");
  addShape(slide, slideNo, "roundRect", 332, 430, 104, 74, SURFACE_ALT, TRANSPARENT, 0, "detail metric 3");
  addShape(slide, slideNo, "roundRect", 96, 522, 340, 50, SURFACE, LINE, 1, "booking planner");
  addText(slide, slideNo, "Gallery, reviews, booking planner, and event details appear on this page.", 112, 536, 306, 22, {
    size: 13,
    color: TEXT,
    role: "detail summary",
  });

  addPanel(slide, slideNo, 492, 248, 220, 350, TEAL, "auth screen");
  addText(slide, slideNo, "Login / Register", 518, 268, 150, 20, {
    size: 15,
    color: TEAL_DARK,
    bold: true,
    face: MONO_FACE,
    role: "screen title",
    checkFit: false,
  });
  addShape(slide, slideNo, "roundRect", 518, 314, 168, 40, SURFACE_ALT, TRANSPARENT, 0, "field 1");
  addShape(slide, slideNo, "roundRect", 518, 370, 168, 40, SURFACE_ALT, TRANSPARENT, 0, "field 2");
  addShape(slide, slideNo, "roundRect", 518, 426, 168, 40, SURFACE_ALT, TRANSPARENT, 0, "field 3");
  addShape(slide, slideNo, "roundRect", 518, 488, 110, 28, "#E6F5F3", TEAL, 1, "otp button");
  addText(slide, slideNo, "Identity input,\nOTP verification,\nand guided sign-in flow", 518, 536, 168, 48, {
    size: 13,
    color: MUTED,
    role: "auth summary",
  });

  addPanel(slide, slideNo, 742, 248, 198, 350, NAVY, "dashboard screen");
  addText(slide, slideNo, "Dashboard", 768, 268, 100, 20, {
    size: 15,
    color: NAVY,
    bold: true,
    face: MONO_FACE,
    role: "screen title",
    checkFit: false,
  });
  for (let i = 0; i < 4; i += 1) {
    addShape(slide, slideNo, "roundRect", 768 + (i % 2) * 72, 314 + Math.floor(i / 2) * 70, 58, 52, SURFACE, LINE, 1, `dash metric ${i + 1}`);
  }
  addShape(slide, slideNo, "roundRect", 768, 458, 146, 106, SURFACE_ALT, TRANSPARENT, 0, "dash panel");
  addText(slide, slideNo, "Role-specific metrics, booking review panels, and management actions.", 768, 580, 150, 34, {
    size: 12,
    color: MUTED,
    role: "dash summary",
  });

  addCard(slide, slideNo, 1000, 256, 206, 332, "Screen interpretation", "The current codebase defines dedicated views for home, venues, venue detail, login, register, OTP verification, bookings, and dashboard operations.", GOLD, 16);
  slide.speakerNotes.setText("Use this slide to talk through the main UI areas shown in the codebase: venue detail interaction, OTP flow, and dashboard management.\n\n[Sources]\n- " + SOURCES.views);
}

function slide12(presentation) {
  const slideNo = 12;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Diagrams",
    "12 / 17",
    "System Flow Diagram",
    "The overall workflow moves from discovery to authentication, booking, storage, and owner or admin review.",
  );
  const labels = ["Open website", "Browse venues", "View details", "Select slot", "OTP login", "Create hold", "Pending booking", "Review decision"];
  const accents = [TEAL, GOLD, CORAL, NAVY, TEAL, GOLD, CORAL, NAVY];
  labels.forEach((label, index) => {
    const x = 78 + index * 110;
    addPanel(slide, slideNo, x, 340, 96, 88, accents[index], `flow ${index + 1}`);
    addText(slide, slideNo, label, x + 14, 366, 68, 34, {
      size: 13,
      color: TEXT,
      align: "center",
      role: "flow label",
    });
    if (index < labels.length - 1) {
      addArrow(slide, slideNo, x + 96, 382, 14, accents[index]);
    }
  });
  addShape(slide, slideNo, "roundRect", 218, 492, 588, 98, SURFACE, LINE, 1, "flow note");
  addText(slide, slideNo, "If the user is not authenticated, the system redirects to login or registration, verifies OTP, and then resumes the booking workflow without losing the intended action.", 246, 522, 534, 44, {
    size: 16,
    color: NAVY,
    role: "flow note text",
  });
  slide.speakerNotes.setText("Walk through the system flow from left to right and pause at OTP verification and slot hold creation, since those are key design decisions.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.script);
}

function slide13(presentation) {
  const slideNo = 13;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Diagrams",
    "13 / 17",
    "Use Case Diagram",
    "EMS2 is organized around customer, owner, and administrator actions with different responsibilities.",
  );
  addNode(slide, slideNo, 86, 280, 188, 190, "Customer", ["Browse venues", "View details", "Register / login", "Book venue", "View bookings", "Submit review"], TEAL);
  addNode(slide, slideNo, 382, 280, 188, 168, "Owner", ["Manage venues", "Update slots", "Review bookings"], GOLD);
  addNode(slide, slideNo, 678, 280, 188, 190, "Administrator", ["Manage users", "Change roles", "Toggle status", "Monitor bookings"], CORAL);
  addNode(slide, slideNo, 972, 316, 188, 130, "Shared platform", ["Dashboard access", "Operational visibility"], NAVY);
  addArrow(slide, slideNo, 274, 354, 108, TEAL);
  addArrow(slide, slideNo, 570, 354, 108, GOLD);
  addArrow(slide, slideNo, 866, 354, 106, CORAL);
  addShape(slide, slideNo, "roundRect", 206, 520, 654, 84, SURFACE_ALT, TRANSPARENT, 0, "use case note");
  addText(slide, slideNo, "This role-based design keeps user journeys focused while still preserving centralized platform control.", 236, 550, 594, 18, {
    size: 17,
    color: NAVY,
    role: "use case note text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Explain the user roles clearly here, because the same role model drives both the UI and access control structure.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.script);
}

function slide14(presentation) {
  const slideNo = 14;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Diagrams",
    "14 / 17",
    "Class and Design View",
    "The PHP application follows a modular structure using core classes, controllers, repositories, and services.",
  );
  addNode(slide, slideNo, 88, 266, 210, 170, "Core layer", ["Config", "Database", "Env", "Router", "Controller"], TEAL);
  addNode(slide, slideNo, 358, 242, 236, 220, "Controllers", ["AuthController", "BookingsController", "DashboardController", "HomeController", "PaymentController"], GOLD);
  addNode(slide, slideNo, 644, 254, 238, 196, "Repositories", ["UserRepository", "VenueRepository", "BookingRepository", "WebhookEventRepository"], CORAL);
  addNode(slide, slideNo, 946, 228, 236, 244, "Services", ["MailOtpService", "VenueCatalogService", "RazorpayWebhookService", "TwilioService", "BookingValidatorClient", "GoogleOAuthService"], NAVY);
  addArrow(slide, slideNo, 298, 350, 60, TEAL);
  addArrow(slide, slideNo, 594, 350, 50, GOLD);
  addArrow(slide, slideNo, 882, 350, 64, CORAL);
  addShape(slide, slideNo, "roundRect", 236, 532, 612, 72, SURFACE, LINE, 1, "design note");
  addText(slide, slideNo, "This separation of responsibilities improves maintainability and keeps request flow, data access, and integration logic organized.", 266, 560, 552, 18, {
    size: 16,
    color: NAVY,
    role: "design note text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Talk about maintainability here. The modular structure is a strong academic point because it shows organized implementation instead of monolithic code.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.readme);
}

function slide15(presentation) {
  const slideNo = 15;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Diagrams",
    "15 / 17",
    "Sequence and Activity View",
    "The booking workflow coordinates UI, controller logic, repositories, and the database in an ordered sequence.",
  );
  const columns = [
    ["Customer", 84, TEAL],
    ["Web UI", 252, GOLD],
    ["Controller", 420, CORAL],
    ["Repository", 588, NAVY],
    ["Database", 756, TEAL_DARK],
  ];
  columns.forEach(([label, x, accent]) => {
    addText(slide, slideNo, label, x, 260, 120, 20, {
      size: 14,
      color: accent,
      bold: true,
      face: MONO_FACE,
      role: "sequence label",
      checkFit: false,
    });
    addShape(slide, slideNo, "rect", x + 52, 292, 2, 250, LINE, TRANSPARENT, 0, "lifeline");
  });
  const steps = [
    [320, 84, 252, "Select venue and slot"],
    [364, 252, 420, "POST booking request"],
    [408, 420, 588, "Resolve slot and validate"],
    [452, 588, 756, "Read / update records"],
    [496, 756, 588, "Return slot state"],
    [540, 588, 420, "Booking saved"],
    [584, 420, 252, "Show payment step"],
  ];
  steps.forEach(([y, from, to, label]) => {
    addArrow(slide, slideNo, from + 54, y, to - from - 54, NAVY);
    addText(slide, slideNo, label, from + 60, y - 20, Math.max(100, to - from - 64), 16, {
      size: 11,
      color: MUTED,
      role: "sequence step",
      checkFit: false,
    });
  });
  addShape(slide, slideNo, "roundRect", 934, 274, 228, 258, SURFACE, LINE, 1, "activity note");
  addText(slide, slideNo, "Activity logic:\n1. Browse venues\n2. Choose slot\n3. Authenticate\n4. Validate availability\n5. Create hold\n6. Create booking\n7. Review outcome", 960, 306, 174, 176, {
    size: 15,
    color: TEXT,
    role: "activity text",
  });
  slide.speakerNotes.setText("Use the sequence portion to explain request flow and the activity summary to reinforce the business process.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.script);
}

function slide16(presentation) {
  const slideNo = 16;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Diagrams",
    "16 / 17",
    "Deployment Diagram",
    "The deployed structure connects a client browser to the PHP web app, the database, and external support services.",
  );
  addNode(slide, slideNo, 88, 318, 178, 122, "Client browser", ["Home, venues, booking, dashboard"], TEAL);
  addNode(slide, slideNo, 372, 286, 236, 184, "PHP web application", ["Apache / XAMPP", "Routing and sessions", "Business logic"], GOLD);
  addNode(slide, slideNo, 714, 318, 178, 122, "MySQL database", ["Users, venues, slots, bookings"], CORAL);
  addNode(slide, slideNo, 968, 238, 192, 110, "SMTP / mail", ["OTP delivery"], NAVY);
  addNode(slide, slideNo, 968, 372, 192, 110, "Razorpay / Twilio / AI / Java", ["Payments, notifications, scoring, validation"], TEAL_DARK);
  addArrow(slide, slideNo, 266, 376, 106, TEAL);
  addArrow(slide, slideNo, 608, 376, 106, GOLD);
  addArrow(slide, slideNo, 892, 284, 76, CORAL);
  addArrow(slide, slideNo, 892, 418, 76, CORAL);
  addShape(slide, slideNo, "roundRect", 226, 534, 612, 70, SURFACE_ALT, TRANSPARENT, 0, "deployment note");
  addText(slide, slideNo, "This diagram shows how the core web application acts as the coordination point between users, storage, and integrations.", 254, 560, 556, 18, {
    size: 16,
    color: NAVY,
    role: "deployment note text",
    checkFit: false,
  });
  slide.speakerNotes.setText("Keep the explanation simple: browser to PHP app, PHP app to database, then outward to services and supporting modules.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.script);
}

function slide17(presentation) {
  const slideNo = 17;
  const slide = baseSlide(
    presentation,
    slideNo,
    "Diagrams",
    "17 / 17",
    "Website Map and Closing Note",
    "The navigation structure leads users from public discovery pages into authentication, booking, and role-based dashboards.",
  );
  addNode(slide, slideNo, 86, 300, 164, 138, "Home", ["Venues", "About", "Contact", "Login", "Register"], TEAL);
  addNode(slide, slideNo, 330, 300, 164, 138, "Venues", ["Venue detail", "Reviews", "Booking planner"], GOLD);
  addNode(slide, slideNo, 574, 300, 164, 138, "Auth", ["OTP verification", "Session start"], CORAL);
  addNode(slide, slideNo, 818, 300, 164, 138, "Bookings", ["Checkout", "Success / failure", "My bookings"], NAVY);
  addNode(slide, slideNo, 1062, 300, 164, 138, "Dashboard", ["Owner operations", "Admin controls", "Review actions"], TEAL_DARK);
  addArrow(slide, slideNo, 250, 366, 80, TEAL);
  addArrow(slide, slideNo, 494, 366, 80, GOLD);
  addArrow(slide, slideNo, 738, 366, 80, CORAL);
  addArrow(slide, slideNo, 982, 366, 80, NAVY);
  addShape(slide, slideNo, "roundRect", 180, 520, 730, 84, NAVY, TRANSPARENT, 0, "closing band");
  addText(slide, slideNo, "EMS2 demonstrates a practical major project with real software engineering structure, user-oriented workflow, and strong scope for future enhancement.", 210, 550, 670, 28, {
    size: 18,
    color: WHITE,
    bold: true,
    role: "closing statement",
  });
  slide.speakerNotes.setText("Use this final slide to summarize the navigation structure and conclude the presentation confidently.\n\n[Sources]\n- " + SOURCES.detailed + "\n- " + SOURCES.script);
}

async function createDeck() {
  await ensureDirs();
  const presentation = Presentation.create({ slideSize: { width: W, height: H } });
  presentation.theme.colorScheme = {
    name: "EMS2",
    themeColors: {
      accent1: TEAL,
      accent2: GOLD,
      accent3: CORAL,
      accent4: NAVY,
      bg1: BG,
      bg2: SURFACE,
      tx1: TEXT,
      tx2: MUTED,
    },
  };
  await slide1(presentation);
  slide2(presentation);
  slide3(presentation);
  slide4(presentation);
  slide5(presentation);
  slide6(presentation);
  slide7(presentation);
  slide8(presentation);
  slide9(presentation);
  await slide10(presentation);
  await slide11(presentation);
  slide12(presentation);
  slide13(presentation);
  slide14(presentation);
  slide15(presentation);
  slide16(presentation);
  slide17(presentation);
  return presentation;
}

async function saveBlobToFile(blob, filePath) {
  const bytes = new Uint8Array(await blob.arrayBuffer());
  await fs.writeFile(filePath, bytes);
}

async function writeInspectArtifact(presentation) {
  const records = [
    { kind: "deck", id: DECK_ID, slideCount: presentation.slides.count, slideSize: { width: W, height: H } },
  ];
  presentation.slides.items.forEach((slide, index) => {
    records.push({ kind: "slide", slide: index + 1, id: slide?.id || `slide-${index + 1}` });
  });
  records.push(...inspectRecords);
  await fs.writeFile(INSPECT_PATH, records.map((record) => JSON.stringify(record)).join("\n") + "\n", "utf8");
}

async function currentRenderLoopCount() {
  try {
    const previous = await fs.readFile(path.join(VERIFICATION_DIR, "render_verify_loops.ndjson"), "utf8");
    return previous.split(/\r?\n/).filter((line) => line.trim()).length;
  } catch {
    return 0;
  }
}

async function appendRenderVerifyLoop(presentation, previewPaths, pptxPath) {
  const logPath = path.join(VERIFICATION_DIR, "render_verify_loops.ndjson");
  const priorCount = await currentRenderLoopCount();
  const record = {
    kind: "render_verify_loop",
    deckId: DECK_ID,
    loop: priorCount + 1,
    maxLoops: MAX_RENDER_VERIFY_LOOPS,
    timestamp: new Date().toISOString(),
    slideCount: presentation.slides.count,
    previewCount: previewPaths.length,
    previewDir: PREVIEW_DIR,
    inspectPath: INSPECT_PATH,
    pptxPath,
  };
  await fs.appendFile(logPath, JSON.stringify(record) + "\n", "utf8");
}

async function verifyAndExport(presentation) {
  const nextLoop = (await currentRenderLoopCount()) + 1;
  if (nextLoop > MAX_RENDER_VERIFY_LOOPS) {
    throw new Error(`Render loop cap reached for ${DECK_ID}.`);
  }
  await writeInspectArtifact(presentation);
  const previewPaths = [];
  for (let index = 0; index < presentation.slides.items.length; index += 1) {
    const slide = presentation.slides.items[index];
    const previewBlob = await presentation.export({ slide, format: "png", scale: 1 });
    const previewPath = path.join(PREVIEW_DIR, `slide-${String(index + 1).padStart(2, "0")}.png`);
    await saveBlobToFile(previewBlob, previewPath);
    previewPaths.push(previewPath);
  }
  const pptxBlob = await PresentationFile.exportPptx(presentation);
  const pptxPath = path.join(OUT_DIR, "output.pptx");
  await pptxBlob.save(pptxPath);
  await appendRenderVerifyLoop(presentation, previewPaths, pptxPath);
  return pptxPath;
}

const presentation = await createDeck();
const pptxPath = await verifyAndExport(presentation);
console.log(pptxPath);
