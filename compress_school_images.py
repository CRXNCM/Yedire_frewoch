import os
import glob
from PIL import Image
import piexif
import re

def rename_and_compress_images(school_folder):
    """
    Renames and compresses all images in a school folder
    """
    # Get the folder name (school name)
    school_name = os.path.basename(school_folder)
    
    # Remove spaces from school name
    school_name = re.sub(r'\s+', '', school_name)
    
    print(f"\nProcessing {school_name}...")
    
    # Get all image files
    image_extensions = ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.webp']
    image_files = []
    
    for ext in image_extensions:
        image_files.extend(glob.glob(os.path.join(school_folder, ext)))
        image_files.extend(glob.glob(os.path.join(school_folder, ext.upper())))
    
    if not image_files:
        print(f"No image files found in {school_folder}")
        return
    
    # Sort files to ensure consistent numbering
    image_files.sort()
    
    print(f"Found {len(image_files)} images")
    
    # Create compressed directory
    compressed_dir = os.path.join(school_folder, "compressed")
    os.makedirs(compressed_dir, exist_ok=True)
    
    # Process each image
    for i, img_path in enumerate(image_files, 1):
        try:
            # Skip files in the compressed directory
            if "compressed" in img_path:
                continue
                
            # New filenames
            new_jpg_filename = f"{school_name}-{i}.jpg"
            new_webp_filename = f"{school_name}-{i}.webp"
            
            jpg_output_path = os.path.join(compressed_dir, new_jpg_filename)
            webp_output_path = os.path.join(compressed_dir, new_webp_filename)
            
            # Open and process image
            img = Image.open(img_path)
            
            # Convert to RGB if needed
            if img.mode in ('RGBA', 'LA', 'P'):
                bg = Image.new('RGB', img.size, (255, 255, 255))
                bg.paste(img, mask=img.split()[3] if img.mode == 'RGBA' else None)
                img = bg
            
            # Resize if larger than max_width (1200px)
            max_width = 1200
            if img.width > max_width:
                ratio = max_width / img.width
                new_height = int(img.height * ratio)
                img = img.resize((max_width, new_height), Image.LANCZOS)
            
            # Create thumbnail version (370px width)
            thumb_dir = os.path.join(compressed_dir, "thumbnails")
            os.makedirs(thumb_dir, exist_ok=True)
            
            thumb_width = 370
            ratio = thumb_width / img.width
            thumb_height = int(img.height * ratio)
            thumb = img.copy()
            thumb = thumb.resize((thumb_width, thumb_height), Image.LANCZOS)
            
            # Try to extract EXIF data
            exif_data = None
            try:
                if 'exif' in img.info:
                    exif_data = img.info['exif']
                elif os.path.splitext(img_path)[1].lower() in ('.jpg', '.jpeg'):
                    try:
                        exif_data = piexif.dump(piexif.load(img_path))
                    except:
                        pass
            except:
                pass
            
            # Save full-size images
            quality = 85
            if exif_data:
                img.save(jpg_output_path, 'JPEG', quality=quality, optimize=True, exif=exif_data)
            else:
                img.save(jpg_output_path, 'JPEG', quality=quality, optimize=True)
            
            img.save(webp_output_path, 'WEBP', quality=quality, method=6)
            
            # Save thumbnails
            thumb.save(os.path.join(thumb_dir, new_jpg_filename), 'JPEG', quality=quality, optimize=True)
            thumb.save(os.path.join(thumb_dir, new_webp_filename), 'WEBP', quality=quality, method=6)
            
            # Calculate compression ratio
            original_size = os.path.getsize(img_path)
            jpg_size = os.path.getsize(jpg_output_path)
            webp_size = os.path.getsize(webp_output_path)
            
            jpg_ratio = (1 - (jpg_size / original_size)) * 100
            webp_ratio = (1 - (webp_size / original_size)) * 100
            
            print(f"Processed {i}/{len(image_files)}: {os.path.basename(img_path)} → {new_jpg_filename}")
            print(f"  Original: {original_size/1024:.1f}KB")
            print(f"  JPG: {jpg_size/1024:.1f}KB ({jpg_ratio:.1f}% smaller)")
            print(f"  WebP: {webp_size/1024:.1f}KB ({webp_ratio:.1f}% smaller)")
            
        except Exception as e:
            print(f"Error processing {img_path}: {e}")

def main():
    # Get current directory
    current_dir = os.getcwd()
    print(f"Looking for school folders in: {current_dir}")
    
    # Find all subdirectories (potential school folders)
    school_folders = [d for d in os.listdir(current_dir) 
                     if os.path.isdir(os.path.join(current_dir, d)) and not d.startswith('.')]
    
    if not school_folders:
        print("No school folders found in the current directory.")
        return
    
    print(f"Found {len(school_folders)} potential school folders:")
    for i, folder in enumerate(school_folders, 1):
        print(f"{i}. {folder}")
    
    print("\nProcessing all folders...")
    
    # Process each school folder
    for folder in school_folders:
        folder_path = os.path.join(current_dir, folder)
        rename_and_compress_images(folder_path)
    
    print("\nAll school folders processed!")
    print("Compressed images and thumbnails are in each school's 'compressed' folder.")

if __name__ == "__main__":
    main()
