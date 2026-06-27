<?php
// خرائط رمز الدولة (CC) -> اسم عربي + علم
function country_map() {
    return [
        '970'=>['name'=>'فلسطين','flag'=>'🇵🇸'],
        '972'=>['name'=>'فلسطين','flag'=>'🇵🇸'],
        '20' =>['name'=>'مصر','flag'=>'🇪🇬'],
        '966'=>['name'=>'السعودية','flag'=>'🇸🇦'],
        '971'=>['name'=>'الإمارات','flag'=>'🇦🇪'],
        '965'=>['name'=>'الكويت','flag'=>'🇰🇼'],
        '974'=>['name'=>'قطر','flag'=>'🇶🇦'],
        '973'=>['name'=>'البحرين','flag'=>'🇧🇭'],
        '968'=>['name'=>'عُمان','flag'=>'🇴🇲'],
        '962'=>['name'=>'الأردن','flag'=>'🇯🇴'],
        '961'=>['name'=>'لبنان','flag'=>'🇱🇧'],
        '963'=>['name'=>'سوريا','flag'=>'🇸🇾'],
        '964'=>['name'=>'العراق','flag'=>'🇮🇶'],
        '967'=>['name'=>'اليمن','flag'=>'🇾🇪'],
        '249'=>['name'=>'السودان','flag'=>'🇸🇩'],
        '218'=>['name'=>'ليبيا','flag'=>'🇱🇾'],
        '216'=>['name'=>'تونس','flag'=>'🇹🇳'],
        '213'=>['name'=>'الجزائر','flag'=>'🇩🇿'],
        '212'=>['name'=>'المغرب','flag'=>'🇲🇦'],
        '90' =>['name'=>'تركيا','flag'=>'🇹🇷'],
        '98' =>['name'=>'إيران','flag'=>'🇮🇷'],
        '1'  =>['name'=>'أمريكا/كندا','flag'=>'🇺🇸'],
        '44' =>['name'=>'بريطانيا','flag'=>'🇬🇧'],
        '49' =>['name'=>'ألمانيا','flag'=>'🇩🇪'],
        '33' =>['name'=>'فرنسا','flag'=>'🇫🇷'],
        '7'  =>['name'=>'روسيا','flag'=>'🇷🇺'],
        '62' =>['name'=>'إندونيسيا','flag'=>'🇮🇩'],
        '91' =>['name'=>'الهند','flag'=>'🇮🇳'],
        '92' =>['name'=>'باكستان','flag'=>'🇵🇰'],
        '880'=>['name'=>'بنغلاديش','flag'=>'🇧🇩'],
        '234'=>['name'=>'نيجيريا','flag'=>'🇳🇬'],
        '27' =>['name'=>'جنوب أفريقيا','flag'=>'🇿🇦'],
    ];
}

function detect_country($phone) {
    $p = ltrim($phone, '+');
    $map = country_map();
    foreach ([4,3,2,1] as $len) {
        $cc = substr($p, 0, $len);
        if (isset($map[$cc])) return ['cc'=>$cc] + $map[$cc];
    }
    return ['cc'=>'??','name'=>'غير معروف','flag'=>'🌐'];
}

function detect_service($text) {
    $t = strtolower($text);
    $services = [
        'WhatsApp'=>['whatsapp','واتس','واتساب'],
        'Facebook'=>['facebook','fb-','فيسبوك'],
        'Telegram'=>['telegram','تليجرام','تلغرام'],
        'Instagram'=>['instagram','انستغرام'],
        'TikTok'=>['tiktok','تيك توك'],
        'Google'=>['google','جوجل','g-'],
        'Twitter/X'=>['twitter',' x ','تويتر'],
        'Snapchat'=>['snapchat','سناب'],
        'Microsoft'=>['microsoft','outlook'],
        'Apple'=>['apple','icloud','ابل'],
        'Signal'=>['signal'],
        'Discord'=>['discord'],
        'PayPal'=>['paypal'],
        'Amazon'=>['amazon'],
        'Netflix'=>['netflix'],
        'Uber'=>['uber'],
        'Viber'=>['viber','فايبر'],
        'IMO'=>['imo '],
        'LinkedIn'=>['linkedin'],
    ];
    foreach ($services as $name=>$kw) {
        foreach ($kw as $k) if (strpos($t, $k) !== false) return $name;
    }
    return 'أخرى';
}

function extract_code($text) {
    if (preg_match('/\b(\d{4,8})\b/', $text, $m)) return $m[1];
    if (preg_match('/(\d{3}[- ]\d{3})/', $text, $m)) return str_replace([' ','-'],'',$m[1]);
    return '';
}
