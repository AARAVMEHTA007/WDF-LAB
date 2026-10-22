import numpy as np
from PIL import Image, ImageDraw, ImageFont

# Load image
img = Image.open("extracted_imgs/page_2_img_1.jpeg").convert("RGBA")
arr = np.array(img, dtype=np.float32)

r, g, b, a = arr[..., 0], arr[..., 1], arr[..., 2], arr[..., 3]

# The warm background is around (246..252, 240..248, 235..242)
# We want to shift warm/cream bg to a fresh light leafy green bg: e.g. (236, 246, 238)
bg_mask = (r > 235) & (g > 230) & (b > 220) & (r > b + 5)
arr[bg_mask, 0] = np.clip(arr[bg_mask, 0] * 0.93, 0, 255) # lower red
arr[bg_mask, 1] = np.clip(arr[bg_mask, 1] * 1.02, 0, 255) # boost green
arr[bg_mask, 2] = np.clip(arr[bg_mask, 2] * 0.96, 0, 255)

# The reddish/terracotta buttons and icon:
# Button color is around r: 120-160, g: 45-75, b: 25-45 (r is much higher than g and b)
# We want leafy green: e.g. (34, 139, 34) or (46, 125, 50)
red_mask = (r > 100) & (r > g * 1.5) & (r > b * 1.8)
# Shift this red to leafy forest green
# Leafy forest green has r ~ 35..50, g ~ 120..155, b ~ 45..65
arr[red_mask, 1] = np.clip(arr[red_mask, 0] * 0.95 + 10, 0, 255) # green becomes high
arr[red_mask, 0] = np.clip(arr[red_mask, 2] * 1.2 + 20, 0, 255)  # red becomes low
arr[red_mask, 2] = np.clip(arr[red_mask, 2] * 1.1 + 10, 0, 255)  # blue modest

# Peach card backgrounds (250, 244, 236):
card_mask = (r > 240) & (g > 235) & (b > 225) & (r > g) & (g > b)
arr[card_mask, 0] = np.clip(arr[card_mask, 0] * 0.94, 0, 255)
arr[card_mask, 1] = np.clip(arr[card_mask, 1] * 1.01, 0, 255)
arr[card_mask, 2] = np.clip(arr[card_mask, 2] * 0.96, 0, 255)

res = Image.fromarray(arr.astype(np.uint8)).convert("RGB")
res.save("extracted_imgs/test_green_1.jpg", quality=95)
print("Saved test_green_1.jpg")
