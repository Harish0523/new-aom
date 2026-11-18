USE new_aom_db;
SET NAMES utf8mb4;

-- Attach the real media files to the seeded gallery items (English rows;
-- other locales inherit these values through the content API fallback).
UPDATE cms_content SET payload_json = JSON_OBJECT(
  'type', 'photo', 'category', 'Meditation',
  'mediaUrl', '/gallery/meditation-circle.jpg', 'icon', 'bi-brightness-alt-high'
) WHERE content_type = 'gallery' AND slug = '1' AND locale = 'en';

UPDATE cms_content SET payload_json = JSON_OBJECT(
  'type', 'photo', 'category', 'Yoga',
  'mediaUrl', '/gallery/yoga-session.jpg', 'icon', 'bi-heart-pulse'
) WHERE content_type = 'gallery' AND slug = '2' AND locale = 'en';

UPDATE cms_content SET payload_json = JSON_OBJECT(
  'type', 'photo', 'category', 'Community',
  'mediaUrl', '/gallery/community-workshop.jpg', 'icon', 'bi-people'
) WHERE content_type = 'gallery' AND slug = '3' AND locale = 'en';

UPDATE cms_content SET payload_json = JSON_OBJECT(
  'type', 'video', 'category', 'Sangeetham',
  'mediaUrl', '/gallery/sangeetham-resonance.mp4', 'poster', '/gallery/sangeetham-thumb.jpg',
  'icon', 'bi-play-circle'
) WHERE content_type = 'gallery' AND slug = '4' AND locale = 'en';

UPDATE cms_content SET payload_json = JSON_OBJECT(
  'type', 'photo', 'category', 'Heritage',
  'mediaUrl', '/gallery/cultural-heritage.jpg', 'icon', 'bi-book'
) WHERE content_type = 'gallery' AND slug = '5' AND locale = 'en';

UPDATE cms_content SET payload_json = JSON_OBJECT(
  'type', 'video', 'category', 'Meditation',
  'mediaUrl', '/gallery/calm-breathing.mp4', 'poster', '/gallery/calm-breathing-thumb.jpg',
  'icon', 'bi-play-circle'
) WHERE content_type = 'gallery' AND slug = '6' AND locale = 'en';

-- Two additional media files that were uploaded to the gallery folder.
INSERT IGNORE INTO cms_content (content_type, slug, locale, title, payload_json, status, sort_order) VALUES
('gallery', '7', 'en', 'Herbal Wellness and Self-Care Rituals', JSON_OBJECT('type', 'photo', 'category', 'Wellness', 'mediaUrl', '/gallery/herbal-wellness.jpg', 'icon', 'bi-flower1'), 'published', 6),
('gallery', '8', 'en', 'Harmonic Peace Sound Journey', JSON_OBJECT('type', 'video', 'category', 'Sangeetham', 'mediaUrl', '/gallery/harmonic-peace.mp4', 'icon', 'bi-play-circle'), 'published', 7),

('gallery', '7', 'ta', 'மூலிகை நல்வாழ்வு மற்றும் சுய பராமரிப்பு', JSON_OBJECT('type', 'photo', 'category', 'நல்வாழ்வு'), 'published', 6),
('gallery', '7', 'fr', 'Bien-être aux herbes et rituels de soin', JSON_OBJECT('type', 'photo', 'category', 'Bien-être'), 'published', 6),
('gallery', '7', 'zh', '草本养生与自我关怀', JSON_OBJECT('type', 'photo', 'category', '养生'), 'published', 6),
('gallery', '7', 'de-CH', 'Kräuter-Wellness und Selbstpflege', JSON_OBJECT('type', 'photo', 'category', 'Wellness'), 'published', 6),

('gallery', '8', 'ta', 'ஒத்திசைவு அமைதி ஒலிப் பயணம்', JSON_OBJECT('type', 'video', 'category', 'சங்கீதம்'), 'published', 7),
('gallery', '8', 'fr', 'Voyage sonore de paix harmonique', JSON_OBJECT('type', 'video', 'category', 'Sangeetham'), 'published', 7),
('gallery', '8', 'zh', '和谐宁静声音之旅', JSON_OBJECT('type', 'video', 'category', '桑吉塔姆音乐'), 'published', 7),
('gallery', '8', 'de-CH', 'Harmonische Friedens-Klangreise', JSON_OBJECT('type', 'video', 'category', 'Sangeetham'), 'published', 7);