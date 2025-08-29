from PIL import Image
import os

# Ścieżka do foldera z obrazkami kategorii
categories_path = r"c:\xampp\htdocs\wordpress\wp-content\themes\krystian_k_sklep\assets\images\categories"

# Pliki do konwersji (duże PNG na JPG)
files_to_convert = [
    "Kultura-i-rozrywka.png",
    "Sport-i-turystyka.png"
]

def convert_png_to_jpg(png_file, quality=85):
    """Konwertuje PNG na JPG z zachowaniem jakości i zmniejszeniem rozmiaru"""
    try:
        # Pełna ścieżka do pliku PNG
        png_path = os.path.join(categories_path, png_file)
        
        # Nowa nazwa pliku JPG
        jpg_file = png_file.replace('.png', '.jpg')
        jpg_path = os.path.join(categories_path, jpg_file)
        
        # Otwórz obraz PNG
        with Image.open(png_path) as img:
            # Konwertuj na RGB (JPG nie obsługuje transparentności)
            if img.mode in ('RGBA', 'LA', 'P'):
                # Tworzymy białe tło
                background = Image.new('RGB', img.size, (255, 255, 255))
                if img.mode == 'P':
                    img = img.convert('RGBA')
                background.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
                img = background
            elif img.mode != 'RGB':
                img = img.convert('RGB')
            
            # Zmniejsz rozmiar jeśli za duży
            max_size = (800, 800)
            img.thumbnail(max_size, Image.Resampling.LANCZOS)
            
            # Zapisz jako JPG z kompresją
            img.save(jpg_path, 'JPEG', quality=quality, optimize=True)
            
            # Sprawdź rozmiary
            png_size = os.path.getsize(png_path) / (1024 * 1024)  # MB
            jpg_size = os.path.getsize(jpg_path) / (1024 * 1024)  # MB
            
            print(f"✅ Konwersja {png_file} zakończona:")
            print(f"   PNG: {png_size:.2f} MB -> JPG: {jpg_size:.2f} MB")
            print(f"   Oszczędność: {((png_size - jpg_size) / png_size * 100):.1f}%")
            
            return jpg_file
            
    except Exception as e:
        print(f"❌ Błąd podczas konwersji {png_file}: {e}")
        return None

def main():
    print("🔄 Rozpoczynam konwersję dużych plików PNG na JPG...")
    print(f"📂 Folder: {categories_path}")
    
    converted_files = []
    
    for png_file in files_to_convert:
        png_path = os.path.join(categories_path, png_file)
        
        if os.path.exists(png_path):
            print(f"\n🖼️  Konwertuję: {png_file}")
            jpg_file = convert_png_to_jpg(png_file)
            if jpg_file:
                converted_files.append((png_file, jpg_file))
        else:
            print(f"⚠️  Plik {png_file} nie istnieje")
    
    if converted_files:
        print(f"\n🎉 Konwersja zakończona! Przekonwertowano {len(converted_files)} plików:")
        for png, jpg in converted_files:
            print(f"   • {png} -> {jpg}")
        
        print(f"\n📝 Pamiętaj o aktualizacji CSS:")
        for png, jpg in converted_files:
            old_name = png.replace('.png', '.png')
            new_name = jpg.replace('.jpg', '.jpg')
            print(f"   background-image: url('./assets/images/categories/{new_name}');")

if __name__ == "__main__":
    main()
