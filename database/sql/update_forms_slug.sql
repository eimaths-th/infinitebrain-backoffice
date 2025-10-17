-- SQL Script สำหรับอัพเดท slug ให้กับฟอร์มที่มีอยู่แล้ว
-- Run หลังจาก migrate เพิ่ม slug column แล้ว

-- อัพเดท slug จาก form_route ที่มีอยู่
UPDATE forms_manage 
SET slug = CASE 
    WHEN form_route = 'gmec' THEN 'gmec'
    WHEN form_route = 'olpcs' THEN 'olpcs'
    WHEN form_route = 'asiarope' THEN 'asiarope'
    WHEN form_route = 'gelosea' THEN 'gelosea'
    WHEN form_route = 'imocsea' THEN 'imocsea'
    WHEN form_route = 'imocsea_special' THEN 'imocsea-special'
    WHEN form_route = 'isocsea' THEN 'isocsea'
    WHEN form_route = 'imocsea+isocsea' THEN 'imocsea-isocsea'
    WHEN form_route = 'cambridge' THEN 'cambridge'
    WHEN form_route = 'huaxiacup' THEN 'huaxiacup'
    WHEN form_route = 'huaxiacup-ii' THEN 'huaxiacup-ii'
    WHEN form_route = 'mathleague' THEN 'mathleague'
    WHEN form_route = 'mathleague-ii' THEN 'mathleague-ii'
    WHEN form_route = 'reward' THEN 'reward'
    WHEN form_route = 'test_field' THEN 'test-field'
    WHEN form_route = 'neos' THEN 'neo-science-olympiad'
    WHEN form_route = 'neos-2' THEN 'neo-science-olympiad-ii'
    WHEN form_route = 'huaxiacup-final-round' THEN 'huaxiacup-final-round'
    WHEN form_route = 'ihcmi' THEN 'international-hope-cup-mathematic-invitational'
    WHEN form_route = 'isc' THEN 'international-stem-competitions'
    WHEN form_route = 'seacso' THEN 'seacso'
    WHEN form_route = 'copernicus' THEN 'copernicus'
    WHEN form_route = 'coding-olympics' THEN 'coding-olympics'
    WHEN form_route = 'stemco_science' THEN 'stemco-science'
    WHEN form_route = 'advance_english_grammar' THEN 'advance-english-grammar'
    WHEN form_route = 'admin-test-field' THEN 'admin-test-field'
    WHEN form_route = 'award-ceremony-isocsea' THEN 'award-ceremony-isocsea'
    WHEN form_route = 'imocsea-national-round' THEN 'imocsea-national-round'
    WHEN form_route = 'isocsea_imocsea_national_round_2025' THEN 'isocsea-imocsea-national-round-2025'
    WHEN form_route = 'isocsea_imocsea_national_round' THEN 'isocsea-imocsea-national-round'
    WHEN form_route = 'testing-form-manage' THEN 'testing-form-manage'
    ELSE LOWER(REPLACE(REPLACE(form_route, '_', '-'), '+', '-'))
END
WHERE slug IS NULL OR slug = '';

-- ตรวจสอบผลลัพธ์
SELECT id, form_name, form_route, slug FROM forms_manage ORDER BY id;

-- อัพเดท opening_closing_time ให้ตรงกับ slug ใหม่ (ถ้าจำเป็น)
-- ตรวจสอบก่อนว่ามี record ไหนที่ route ไม่ตรงกับ slug
SELECT oct.id, oct.route, fm.slug, fm.form_name
FROM opening_closing_time oct
LEFT JOIN forms_manage fm ON oct.route = fm.form_route
WHERE fm.slug IS NOT NULL AND oct.route != fm.slug;

-- ถ้าต้องการอัพเดท opening_closing_time ให้ตรงกับ slug ใหม่
-- ให้ uncomment และ run คำสั่งด้านล่าง
/*
UPDATE opening_closing_time oct
INNER JOIN forms_manage fm ON oct.route = fm.form_route
SET oct.route = fm.slug
WHERE fm.slug IS NOT NULL;
*/

-- ตรวจสอบว่ามี slug ซ้ำหรือไม่
SELECT slug, COUNT(*) as count 
FROM forms_manage 
WHERE slug IS NOT NULL 
GROUP BY slug 
HAVING count > 1;

-- ถ้ามี slug ซ้ำ ให้เพิ่มหมายเลขต่อท้าย
-- (ควร run ผ่าน PHP/Laravel เพื่อความปลอดภัย)
