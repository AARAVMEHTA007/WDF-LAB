import os
import numpy as np
from PIL import Image, ImageDraw, ImageFont

os.makedirs("modified_imgs", exist_ok=True)

# Fonts
font_tahoma_10 = ImageFont.truetype("tahoma.ttf", 10)
font_tahoma_11 = ImageFont.truetype("tahoma.ttf", 11)
font_tahoma_12 = ImageFont.truetype("tahoma.ttf", 12)
font_tahoma_bd_11 = ImageFont.truetype("tahomabd.ttf", 11)
font_segoe_11 = ImageFont.truetype("segoeui.ttf", 11)
font_segoe_12 = ImageFont.truetype("segoeui.ttf", 12)
font_segoe_13 = ImageFont.truetype("segoeui.ttf", 13)
font_segoe_bd_13 = ImageFont.truetype("segoeuib.ttf", 13)

# ==========================================
# 1. IMAGE 1: page_2_img_1.jpeg (DB Verifier)
# Transform to Leafy Green + change row counts
# ==========================================
print("1. Generating Leafy Green DB Verifier...")
img1 = Image.open("extracted_imgs/page_2_img_1.jpeg").convert("RGBA")
arr1 = np.array(img1, dtype=np.float32)
r, g, b, a = arr1[..., 0], arr1[..., 1], arr1[..., 2], arr1[..., 3]

# Leafy background tint
bg_mask = (r > 235) & (g > 230) & (b > 220) & (r > b + 5)
arr1[bg_mask, 0] = np.clip(arr1[bg_mask, 0] * 0.93, 0, 255)
arr1[bg_mask, 1] = np.clip(arr1[bg_mask, 1] * 1.02, 0, 255)
arr1[bg_mask, 2] = np.clip(arr1[bg_mask, 2] * 0.96, 0, 255)

# Button and badges to leafy green
red_mask = (r > 100) & (r > g * 1.5) & (r > b * 1.8)
arr1[red_mask, 1] = np.clip(arr1[red_mask, 0] * 0.95 + 10, 0, 255) # Green
arr1[red_mask, 0] = np.clip(arr1[red_mask, 2] * 1.2 + 20, 0, 255)  # Red
arr1[red_mask, 2] = np.clip(arr1[red_mask, 2] * 1.1 + 10, 0, 255)  # Blue

# Cards
card_mask = (r > 240) & (g > 235) & (b > 225) & (r > g) & (g > b)
arr1[card_mask, 0] = np.clip(arr1[card_mask, 0] * 0.94, 0, 255)
arr1[card_mask, 1] = np.clip(arr1[card_mask, 1] * 1.01, 0, 255)
arr1[card_mask, 2] = np.clip(arr1[card_mask, 2] * 0.96, 0, 255)

img1_mod = Image.fromarray(arr1.astype(np.uint8)).convert("RGB")
draw1 = ImageDraw.Draw(img1_mod)

badge_bg = (235, 248, 239)
badge_border = (195, 232, 205)
badge_text = (38, 115, 77)

def draw_pill(draw, box, text, font, bg_color, border_color, text_color):
    draw.rounded_rectangle(box, radius=8, fill=bg_color, outline=border_color, width=1)
    bbox = font.getbbox(text)
    tw = bbox[2] - bbox[0]
    th = bbox[3] - bbox[1]
    tx = box[0] + (box[2] - box[0] - tw) // 2
    ty = box[1] + (box[3] - box[1] - th) // 2 - bbox[1]
    draw.text((tx, ty), text, font=font, fill=text_color)

# Card 1 badge (students 18 rows)
draw1.rectangle([468, 366, 545, 395], fill=(236, 246, 238))
draw_pill(draw1, [470, 368, 542, 393], "18 rows", font_segoe_bd_13, badge_bg, badge_border, badge_text)

# Card 2 badge (events 5 rows)
draw1.rectangle([662, 366, 735, 395], fill=(236, 246, 238))
draw_pill(draw1, [665, 368, 730, 393], "5 rows", font_segoe_bd_13, badge_bg, badge_border, badge_text)

# Card 3 badge (registrations 3 rows) - box at 858..900, 368..412
draw1.rectangle([856, 366, 902, 414], fill=(236, 246, 238))
draw1.rounded_rectangle([858, 368, 900, 412], radius=8, fill=badge_bg, outline=badge_border, width=1)
draw1.text((875, 370), "3", font=font_segoe_bd_13, fill=badge_text)
draw1.text((864, 389), "rows", font=font_segoe_13, fill=badge_text)

# Card 4 badge (contact_inquiries 2 rows) - box at 1048..1090, 368..412
draw1.rectangle([1046, 366, 1092, 414], fill=(236, 246, 238))
draw1.rounded_rectangle([1048, 368, 1090, 412], radius=8, fill=badge_bg, outline=badge_border, width=1)
draw1.text((1065, 370), "2", font=font_segoe_bd_13, fill=badge_text)
draw1.text((1054, 389), "rows", font=font_segoe_13, fill=badge_text)

img1_mod.save("modified_imgs/output_1_db_verifier.png", quality=95)
print("  Saved output_1_db_verifier.png")

# ==========================================
# 2. IMAGE 2: page_2_img_2.jpeg (Table Listing)
# ==========================================
print("2. Generating Table Listing...")
img2 = Image.open("extracted_imgs/page_2_img_2.jpeg").convert("RGB")
draw2 = ImageDraw.Draw(img2)

def patch_num(draw, x, y, old_box_w, old_box_h, text, bg_col, font):
    draw.rectangle([x, y, x + old_box_w, y + old_box_h], fill=bg_col)
    draw.text((x + 2, y + 1), text, font=font, fill=(0, 0, 0))

patch_num(draw2, 622, 311, 20, 16, "8", (255, 255, 255), font_tahoma_12)
patch_num(draw2, 622, 347, 20, 16, "2", (245, 245, 245), font_tahoma_12)
patch_num(draw2, 622, 383, 20, 16, "5", (255, 255, 255), font_tahoma_12)
patch_num(draw2, 622, 420, 20, 16, "3", (245, 245, 245), font_tahoma_12)
patch_num(draw2, 615, 456, 26, 16, "18", (255, 255, 255), font_tahoma_12)

# Total sum cell at bottom:
draw2.rectangle([605, 526, 645, 545], fill=(235, 235, 235))
draw2.text((610, 528), "~36", font=font_tahoma_bd_11, fill=(0, 0, 0))

img2.save("modified_imgs/output_2_table_listing.png", quality=95)
print("  Saved output_2_table_listing.png")

# ==========================================
# 3. IMAGE 3: page_3_img_1.jpeg (ER Designer)
# ==========================================
print("3. Generating ER Diagram...")
img3 = Image.open("extracted_imgs/page_3_img_1.jpeg").convert("RGB")
draw3 = ImageDraw.Draw(img3)

# registrations box ticket_code & status
draw3.rectangle([212, 725, 290, 742], fill=(245, 245, 245))
draw3.text((214, 727), "varchar(50)", font=font_tahoma_11, fill=(50, 50, 50))

draw3.rectangle([182, 748, 380, 765], fill=(230, 230, 230))
draw3.text((184, 750), "enum('confirmed','cancelled')", font=font_tahoma_11, fill=(50, 50, 50))

img3.save("modified_imgs/output_3_er_diagram.png", quality=95)
print("  Saved output_3_er_diagram.png")

# ==========================================
# 4. IMAGE 4: page_4_img_1.jpeg (Students Schema)
# ==========================================
print("4. Generating Students Schema...")
img4 = Image.open("extracted_imgs/page_4_img_1.jpeg").convert("RGB")
draw4 = ImageDraw.Draw(img4)

draw4.rectangle([590, 222, 735, 255], fill=(245, 245, 245))
draw4.text((592, 224), "Unique formatted ID e.g. STU-", font=font_tahoma_11, fill=(80, 80, 80))
draw4.text((592, 238), "101, STU-102", font=font_tahoma_11, fill=(80, 80, 80))

img4.save("modified_imgs/output_4_students_schema.png", quality=95)
print("  Saved output_4_students_schema.png")

# ==========================================
# 5. IMAGE 5: page_4_img_2.jpeg (Students Data Seed)
# ==========================================
print("5. Generating Students Seeded Data...")
img5 = Image.open("extracted_imgs/page_4_img_2.jpeg").convert("RGB")
draw5 = ImageDraw.Draw(img5)

# Banner: at y = 56..75
draw5.rectangle([82, 56, 360, 75], fill=(212, 237, 218))
draw5.text((95, 59), "Showing rows 0 - 17 (18 total, Query took 0.0003 seconds.)", font=font_tahoma_11, fill=(21, 87, 36))

# In student table rows: exact starting at y = 136
new_students = [
    ("1", "ADM-001", "Aarav", "Mehta", "aarav.mehta@studenthub.ac.in", "STAFF01", "Academic Affairs & IT", "0", "+91 98765 00000", "StudentHub Administrator"),
    ("2", "STU-101", "Krunal", "Patel", "krunal.patel@charusat.edu.in", "22IT104", "Information Tech.", "5", "+91 98251 11204", "Cloud computing & DevOps"),
    ("3", "STU-102", "Riya", "Shah", "riya.shah@charusat.edu.in", "22CE088", "Computer Eng.", "5", "+91 98253 44102", "Full stack web developer"),
    ("4", "STU-103", "Dev", "Trivedi", "dev.trivedi@charusat.edu.in", "22EC045", "Electronics & Comm.", "5", "+91 98254 55301", "Embedded IoT researcher"),
    ("5", "STU-104", "Harshil", "Joshi", "harshil.joshi@charusat.edu.in", "21IT092", "Information Tech.", "7", "+91 98255 66710", "Cybersecurity analyst"),
    ("6", "STU-105", "Tanvi", "Shah", "tanvi.shah@charusat.edu.in", "22CL014", "Civil Engineering", "5", "+91 98256 77821", "Smart infrastructure CAD"),
    ("7", "STU-106", "Manan", "Bhatt", "manan.bhatt@charusat.edu.in", "22ME052", "Mechanical Eng.", "5", "+91 98257 88915", "Sustainable automotive"),
    ("8", "STU-107", "Disha", "Parekh", "disha.parekh@charusat.edu.in", "22IT067", "Information Tech.", "5", "+91 98258 99011", "Big data pipeline optimization")
]

row_y_starts = [136, 161, 186, 211, 236, 261, 286, 311]

for idx, (sid, scode, fname, lname, email, roll, dept, sem, phone, bio) in enumerate(new_students):
    y = row_y_starts[idx]
    bg = (255, 255, 255) if idx % 2 == 0 else (245, 245, 245)
    
    # ID: x ~ 165..185
    draw5.rectangle([165, y+2, 185, y+20], fill=bg)
    draw5.text((168, y+4), sid, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Code: x ~ 188..235
    draw5.rectangle([188, y+2, 235, y+20], fill=bg)
    draw5.text((190, y+4), scode, font=font_tahoma_11, fill=(0, 0, 0))
    
    # First name: x ~ 238..282
    draw5.rectangle([238, y+2, 282, y+20], fill=bg)
    draw5.text((240, y+4), fname, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Last name: x ~ 285..325
    draw5.rectangle([285, y+2, 325, y+20], fill=bg)
    draw5.text((287, y+4), lname, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Email: x ~ 330..470
    draw5.rectangle([330, y+2, 470, y+20], fill=bg)
    draw5.text((332, y+4), email, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Roll number: x ~ 535..580
    draw5.rectangle([535, y+2, 580, y+20], fill=bg)
    draw5.text((537, y+4), roll, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Department: x ~ 585..680
    draw5.rectangle([585, y+2, 680, y+20], fill=bg)
    draw5.text((587, y+4), dept, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Semester: x ~ 685..700
    draw5.rectangle([685, y+2, 700, y+20], fill=bg)
    draw5.text((687, y+4), sem, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Phone: x ~ 705..765
    draw5.rectangle([705, y+2, 765, y+20], fill=bg)
    draw5.text((707, y+4), phone, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Bio: x ~ 770..910
    draw5.rectangle([770, y+2, 910, y+20], fill=bg)
    draw5.text((772, y+4), bio, font=font_tahoma_11, fill=(0, 0, 0))

img5.save("modified_imgs/output_5_students_data.png", quality=95)
print("  Saved output_5_students_data.png")

# ==========================================
# 6. IMAGE 6: page_5_img_1.jpeg (Events Schema)
# ==========================================
print("6. Generating Events Schema...")
img6 = Image.open("extracted_imgs/page_5_img_1.jpeg").convert("RGB")
draw6 = ImageDraw.Draw(img6)

# Badge default: 'Event' -> 'Featured'
draw6.rectangle([580, 342, 630, 360], fill=(245, 245, 245))
draw6.text((582, 344), "Featured", font=font_tahoma_11, fill=(80, 80, 80))

# Max capacity default: 100 -> 120
draw6.rectangle([580, 488, 620, 508], fill=(245, 245, 245))
draw6.text((582, 490), "120", font=font_tahoma_11, fill=(80, 80, 80))

# Bottom cardinality table: Cardinality 6 -> 5
draw6.rectangle([535, 955, 555, 975], fill=(255, 255, 255))
draw6.text((538, 958), "5", font=font_tahoma_11, fill=(0, 0, 0))

img6.save("modified_imgs/output_6_events_schema.png", quality=95)
print("  Saved output_6_events_schema.png")

# ==========================================
# 7. IMAGE 7: page_5_img_2.jpeg (Events Catalog Data)
# ==========================================
print("7. Generating Events Catalog Data...")
img7 = Image.open("extracted_imgs/page_5_img_2.jpeg").convert("RGB")
draw7 = ImageDraw.Draw(img7)

# Banner: y = 62..82
draw7.rectangle([82, 62, 360, 82], fill=(212, 237, 218))
draw7.text((95, 65), "Showing rows 0 - 4 (5 total, Query took 0.0003 seconds.)", font=font_tahoma_11, fill=(21, 87, 36))

new_events = [
    ("1", "Cloud Computing & DevOps Summit", "Hands-on containerization, Docker, Kubernetes and CI/CD...", "Tech & Innovation", "Cloud", "Seminar Hall 1, IT Dept", "2026-10-15 10:00:00", "120"),
    ("2", "Annual Techfest 2026 (InnovateX)", "Premier technical symposium featuring competitive coding...", "Tech & Innovation", "Fest", "Main University Arena", "2026-10-20 09:30:00", "350"),
    ("3", "Modern Full-Stack PHP & Laravel Workshop", "Building scalable MVC web applications and REST APIs...", "Tech & Innovation", "Web Dev", "Computer Lab 4, Tech Block", "2026-10-24 14:00:00", "90"),
    ("4", "Cyber Security & Ethical Hacking Symposium", "Interactive red-teaming, cryptography, and network defense...", "Tech & Innovation", "Security", "Auditorium 2, Science Wing", "2026-10-29 11:00:00", "110"),
    ("5", "AI & Generative Intelligence Summit", "Exploring LLM agentic architectures and neural networks...", "Tech & Innovation", "AI", "Tech Hub Amphitheatre", "2026-11-05 10:00:00", "180"),
]

event_y_starts = [168, 203, 238, 273, 308]

for idx, (eid, etitle, edesc, ecat, ebadge, eloc, edt, ecap) in enumerate(new_events):
    y = event_y_starts[idx]
    bg = (255, 255, 255) if idx % 2 == 0 else (245, 245, 245)
    
    # ID: x ~ 225..240
    draw7.rectangle([225, y+2, 240, y+24], fill=bg)
    draw7.text((228, y+4), eid, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Title: x ~ 245..440
    draw7.rectangle([245, y+2, 440, y+24], fill=bg)
    draw7.text((247, y+4), etitle[:32], font=font_tahoma_11, fill=(0, 0, 0))
    
    # Description: x ~ 445..620
    draw7.rectangle([445, y+2, 620, y+24], fill=bg)
    draw7.text((447, y+4), edesc[:36] + "...", font=font_tahoma_11, fill=(0, 0, 0))
    
    # Category: x ~ 625..700
    draw7.rectangle([625, y+2, 700, y+24], fill=bg)
    draw7.text((627, y+4), ecat, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Badge: x ~ 705..740
    draw7.rectangle([705, y+2, 740, y+24], fill=bg)
    draw7.text((707, y+4), ebadge, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Location: x ~ 745..885
    draw7.rectangle([745, y+2, 885, y+24], fill=bg)
    draw7.text((747, y+4), eloc, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Event Date: x ~ 890..1015
    draw7.rectangle([890, y+2, 1015, y+24], fill=bg)
    draw7.text((892, y+4), edt, font=font_tahoma_11, fill=(0, 0, 0))
    
    # Capacity: x ~ 1245..1280
    draw7.rectangle([1245, y+2, 1280, y+24], fill=bg)
    draw7.text((1248, y+4), ecap, font=font_tahoma_11, fill=(0, 0, 0))

# Clean out row 6
draw7.rectangle([100, 343, 1400, 378], fill=(255, 255, 255))

img7.save("modified_imgs/output_7_events_data.png", quality=95)
print("  Saved output_7_events_data.png")

# ==========================================
# 8. IMAGE 8: page_6_img_1.jpeg (Registrations Schema)
# ==========================================
print("8. Generating Registrations Schema...")
img8 = Image.open("extracted_imgs/page_6_img_1.jpeg").convert("RGB")
draw8 = ImageDraw.Draw(img8)

# ticket_code: varchar(64) -> varchar(50)
draw8.rectangle([275, 495, 360, 515], fill=(245, 245, 245))
draw8.text((278, 497), "varchar(50)", font=font_tahoma_11, fill=(80, 80, 80))

# status: enum('confirm', 'not-confirm') -> enum('confirmed', 'pending', 'cancelled')
draw8.rectangle([275, 545, 480, 565], fill=(255, 255, 255))
draw8.text((278, 547), "enum('confirmed','pending','cancelled')", font=font_tahoma_11, fill=(80, 80, 80))

# default status: confirm -> confirmed
draw8.rectangle([540, 545, 600, 565], fill=(255, 255, 255))
draw8.text((542, 547), "confirmed", font=font_tahoma_11, fill=(80, 80, 80))

img8.save("modified_imgs/output_8_registrations_schema.png", quality=95)
print("  Saved output_8_registrations_schema.png")

# ==========================================
# 9. IMAGE 9: page_6_img_2.jpeg (Registrations Data)
# ==========================================
print("9. Generating Registrations Data...")
img9 = Image.open("extracted_imgs/page_6_img_2.jpeg").convert("RGB")
draw9 = ImageDraw.Draw(img9)

# Banner: at y = 72..92
draw9.rectangle([82, 72, 360, 92], fill=(212, 237, 218))
draw9.text((95, 75), "Showing rows 0 - 1 (2 total, Query took 0.0003 seconds.)", font=font_tahoma_11, fill=(21, 87, 36))

new_regs = [
    ("11", "18", "1", "2026-10-15 10:00:00", "SH-2026-REG-78A4F", "confirmed", "2026-10-18 09:15:22", "2026-10-18 09:15:22"),
    ("12", "22", "2", "2026-10-20 09:30:00", "SH-2026-REG-91B2C", "confirmed", "2026-10-18 10:42:05", "2026-10-18 10:42:05"),
]

reg_y = [265, 295]

for idx, (rid, sid, eid, edate, tcode, stat, regat, updat) in enumerate(new_regs):
    y = reg_y[idx]
    bg = (255, 255, 255) if idx % 2 == 0 else (245, 245, 245)
    
    # reg_id: x ~ 348..375
    draw9.rectangle([348, y+2, 375, y+22], fill=bg)
    draw9.text((350, y+4), rid, font=font_tahoma_11, fill=(0, 0, 0))
    
    # student_id: x ~ 405..430
    draw9.rectangle([405, y+2, 430, y+22], fill=bg)
    draw9.text((408, y+4), sid, font=font_tahoma_11, fill=(0, 0, 0))
    
    # event_id: x ~ 460..485
    draw9.rectangle([460, y+2, 485, y+22], fill=bg)
    draw9.text((463, y+4), eid, font=font_tahoma_11, fill=(0, 0, 0))
    
    # event_date: x ~ 490..620
    draw9.rectangle([490, y+2, 620, y+22], fill=bg)
    draw9.text((493, y+4), edate, font=font_tahoma_11, fill=(0, 0, 0))
    
    # ticket_code: x ~ 625..750
    draw9.rectangle([625, y+2, 750, y+22], fill=bg)
    draw9.text((628, y+4), tcode, font=font_tahoma_11, fill=(0, 0, 0))
    
    # status: x ~ 755..810
    draw9.rectangle([755, y+2, 810, y+22], fill=bg)
    draw9.text((758, y+4), stat, font=font_tahoma_11, fill=(0, 0, 0))
    
    # registered_at: x ~ 815..940
    draw9.rectangle([815, y+2, 940, y+22], fill=bg)
    draw9.text((818, y+4), regat, font=font_tahoma_11, fill=(0, 0, 0))
    
    # updated_at: x ~ 945..1070
    draw9.rectangle([945, y+2, 1070, y+22], fill=bg)
    draw9.text((948, y+4), updat, font=font_tahoma_11, fill=(0, 0, 0))

img9.save("modified_imgs/output_9_registrations_data.png", quality=95)
print("  Saved output_9_registrations_data.png")

print("\nALL 9 IMAGES CUSTOMIZED PERFECTLY!")
