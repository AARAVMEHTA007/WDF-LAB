import fitz
import os

pdf_path = r"C:\Users\AARAV MEHTA\OneDrive\Desktop\AARAV(WDF-LAB)\D26DCE152-Practical-8.pdf"
doc = fitz.open(pdf_path)
os.makedirs("extracted_imgs", exist_ok=True)

print(f"Total pages: {len(doc)}")
img_list = []
for i, page in enumerate(doc):
    page_imgs = page.get_images()
    print(f"Page {i+1} has {len(page_imgs)} images")
    for j, img in enumerate(page_imgs):
        xref = img[0]
        base = doc.extract_image(xref)
        ext = base['ext']
        out_name = f"extracted_imgs/page_{i+1}_img_{j+1}.{ext}"
        with open(out_name, "wb") as f:
            f.write(base['image'])
        img_list.append((i+1, j+1, out_name, base['width'], base['height']))
        print(f"  Extracted {out_name} - {base['width']}x{base['height']}")
