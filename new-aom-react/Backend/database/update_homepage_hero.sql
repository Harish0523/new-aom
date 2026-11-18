USE new_aom_db;
SET NAMES utf8mb4;

UPDATE cms_content
SET title = 'Discover the Wisdom Within',
    payload_json = JSON_OBJECT(
      'eyebrow', 'Spirituality, Wisdom, Meditation & Cultural Harmony',
      'body', 'Meditation, AUM Chanting, Yoga, Personal Development, Cultural Heritage, and Community Services.',
      'button_label', 'Explore Our Services',
      'button_url', '/services'
    )
WHERE content_type = 'homepage' AND locale = 'en';

UPDATE cms_content
SET title = 'உள்ளார்ந்த அமைதியையும் காலத்தால் அழியாத ஞானத்தையும் உணருங்கள்',
    payload_json = JSON_OBJECT(
      'eyebrow', 'ஆன்மீகம், ஞானம், தியானம் மற்றும் பண்பாட்டு நல்லிணக்கம்',
      'body', 'தியானம், ஓம் மந்திர உச்சாடனம், யோகா, ஆளுமை வளர்ச்சி, பண்பாட்டு மரபு மற்றும் சமூக நலப் பணிகள்.',
      'button_label', 'எங்கள் சேவைகளைக் காண்க',
      'button_url', '/services'
    )
WHERE content_type = 'homepage' AND locale = 'ta';

UPDATE cms_content
SET title = 'Éveillez le calme intérieur et la sagesse éternelle',
    payload_json = JSON_OBJECT(
      'eyebrow', 'Spiritualité, sagesse, méditation et harmonie culturelle',
      'body', 'Méditation, chant AUM, yoga, développement personnel, héritage culturel et services communautaires.',
      'button_label', 'Découvrir nos services',
      'button_url', '/services'
    )
WHERE content_type = 'homepage' AND locale = 'fr';

UPDATE cms_content
SET title = '唤醒内在宁静与永恒智慧',
    payload_json = JSON_OBJECT(
      'eyebrow', '灵性、智慧、冥想与文化和谐',
      'body', '冥想、AUM 唱诵、瑜伽、个人成长、文化传承与社区服务。',
      'button_label', '浏览我们的服务',
      'button_url', '/services'
    )
WHERE content_type = 'homepage' AND locale = 'zh';

UPDATE cms_content
SET title = 'Innere Ruhe und zeitlose Weisheit entdecken',
    payload_json = JSON_OBJECT(
      'eyebrow', 'Spiritualität, Weisheit, Meditation und kulturelle Harmonie',
      'body', 'Meditation, AUM-Gesang, Yoga, persönliche Entwicklung, Kulturerbe und Gemeinschaftsdienste.',
      'button_label', 'Unsere Angebote ansehen',
      'button_url', '/services'
    )
WHERE content_type = 'homepage' AND locale = 'de-CH';
