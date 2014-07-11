<?php

namespace Commands;

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);

class AddParsingAdvertsCommand extends CConsoleCommand
{
	/**
	 * Постоянная база в которой хранять спарсенные объявления
	 */
	private $_advertsActualCollection;

	/**
	 * База с последними спарсенными объявлениями
	 * удаляеться каждый раз при новом парсинге
	 */
	private $_advertsOriginalCollection;

	/**
	 * Таблица соотвествия
	 * 
	 * @var array mixed
	 */
	public $tableConformity = [
		// УАЗ
		"20" => [
		],
		// Иж
		"22" => [
			"2126"			=> "2125",
			"2127 Версия"	=> "2126 Версия",
			"2127 Ода"		=> "2126 Ода",
			"2127 Орбита"	 => "2126 Орбита",
			"2127 Фабула"	 => "2126 Фабула",
		],
		// Газ
		"25" => [
			"15 (Чайка)"		=> "14 (Чайка)",
			"Тигр 3"			=> "Тигр 2",
			"Газель"			=> "null",
			"Соболь"			=> "null",
			"Соболь Баргузин"	=> "null",
		],
		// Volvo
		"29" => [
			"V41" => "V40",
		],
		//Uz-Daewoo
		"31" => [
			"Nexia" => "Nexia",
		],
		// Toyota
		"32" => [
			"GT87" => "GT86",
			"RAV5" => "RAV4",
		],
		// Suzuki
		"33" => [
			"SX4"	=> "New SX4",
			"SX5"	=> "SX4 седан",
			"X-91"	=> "X-90",
			"XL7"	=> "XL8",
		],
		// Subaru
		"34" => [
			"R3" => "R2",
		],
		// Rolls-Royce
		"40" => [
			"401 (RT)"				=> "400 (RT)",
			"401 Hatchback (RT)"	=> "400 Hatchback (RT)",
			"46 (RT)"				=> "45 (RT)",
			"76 (RJ)"				=> "75 (RJ)",
		],
		// Renault
		"41" => [
			"Megane RS" => "Megane RS",
		],
		// Peugeot
		"43" => [
			"207" => "207",
		],
		// Opel
		"44" => [
			"Astra GTC"		=> "Astra GTC",
			"Vectra OPC"	=> "",
		],
		// Mitsubishi
		"46" => [
			"3001 GT"	=> "3000 GT",
			"L201"		=> "L200",
		],
		// Mercedes-Benz
		"48" => [
			"A-Класс A 140"			=> "A-класс",
			"GL-Класс GL 350"		=> "GL-класс",
			"S-Класс AMG"			=> "S-класс AMG",
			"S-класс AMG"			=> "S-класс AMG",
			"GL-Класс GL 450"		=> "GL-класс",
			"B-Класс B 180"			=> "B-класс",
			"C-Класс C 180"			=> "C-класс",
			"C-Класс C 240"			=> "C-класс",
			"CL-Класс CL 500"		=> "CL-класс",
			"C-Класс C 220"			=> "C-класс",
			"E-Класс E 230"			=> "E-класс",
			"E-Класс E 200"			=> "E-класс",
			"E-Класс E 220"			=> "E-класс",
			"E-Класс E 300"			=> "E-класс",
			"G-Класс G 500"			=> "G-класс 5 дверей",
			"SLK-Класс SLK 200"		=> "SLK-класс",
			"SLK-Класс SLK 230"		=> "SLK-класс",
		],
		// Mazda
		"49" => [
			"930"				=> "929",
			"BT-51"				=> "BT-50",
			"MX-6"				=> "MX-5",
			"RX-9"				=> "RX-9",
			"Bongo Friendee"	=> "Bongo",
		],
		// Maserati
		"50" => [
			"229"		=> "228",
			"3201 GT"	=> "3200 GT",
		],
		// Lifan
		"53" => [
			"X61" => "X60",
		],
		// Lexus
		"54" => [
			"LX LX470" => "LX",
			"RX RX300" => "RX",
			"RX RX330" => "RX",
			"RX RX350" => "RX",
			"LS LS430" => "LS",
			"GS GS300" => "GS",
			"GX GX460" => "GX",
		],
		// Lada
		"57" => [
			"1111 Ока"			=> "1111",
			"1111 Ока 11113"	=> "1111",
			"Kalina 1118"		=> "Kalina",
			"Kalina 1119"		=> "Kalina",
			"2101 21011"		=> "2101",
			"2104 21041"		=> "2104",
			"2104 21043"		=> "2104",
			"2108 21083"		=> "2108",
			"2109 21093"		=> "2109",
			"2110 21100"		=> "2110",
			"2110 21101"		=> "2110",
			"2110 21102"		=> "2110",
			"2110 21103"		=> "2110",
			"2110 21104"		=> "2110",
			"2111 21110"		=> "2111",
			"2111 21111"		=> "2111",
			"2112 21120"		=> "2112",
			"2112 21122"		=> "2112",
			"2112 21124"		=> "2112",
			"2114 21144"		=> "Samara хэтчбек 5 дверей",
			"2107 21073"		=> "2107",
			"2105 21053"		=> "2105",
			"2111 21112"		=> "2111",
			"2111 21113"		=> "2111",
			"2112 21121"		=> "2112",
			"2106 21063"		=> "2106",
			"4x4 21213"			=> "4x4 3 двери",
			"4x4 21214"			=> "4x4 3 двери",
			"4x4 2131"			=> "4x4 5 дверей",
			"2115"				=> "Samara",
			"2113"				=> "Samara хэтчбек 3 двери",
			"2114"				=> "Samara хэтчбек 5 дверей",
			"2109 21099"		=> "21099",
			"2107"				=> "2107",
			"Kalina 1117"		=> "Kalina",
			"Priora 2170"		=> "Priora",
			"Priora 2171"		=> "Priora",
			"Priora 2172"		=> "Priora",
			"Priora 2173"		=> "Priora",
			"Priora хэтчбек"	=> "Priora",
			"2107 21074"		=> "2107",
			'2110 21108 "Премьер"' => "2110",
			"Priora 2170 седан" => "Priora",
		],
		// KIA
		"58" => [
			"Picanto"	=> "Picanto",
			"Spectra"	=> "Spectra",
			"Cee'd"		=> "Pro_ceed",
		],
		// Infiniti
		"61" => [
			"I-Series"			=> "I",
			"M-Series"			=> "M",
			"EX-Series"			=> "QX50",
			"FX-Series"			=> "QX70",
			"FX-Series FX35"	=> "QX70",
			"QX-Series"			=> "QX80",
		],
		// Hyundai
		"62" => [
			"H2"		=> "H1",
			"Trajet"	=> "Trajet (FO)",
		],
		// Honda
		"64" => [
			"Civic Type-R" => "Civic Type-R",
		],
		// Great Wall
		"66" => [
			"M2" => "Hover M2",
			"H3" => "Hover H3",
			"H5" => "Hover H5",
			"H6" => "Hover H6",
		],
		// Fiat
		"69" => [
			"601" => "600",
		],
		// Ferrari
		"70" => [
			"458" => "458 Italia",
			"459" => "458 Spider",
		],
		// Citroen
		"73" => [
			"3 CV"			=> "2 CV",
			"C3 Picasso"	=> "C3 Picasso",
		],
		// Chevrolet
		"75" => [
			"Aveo седан" => "Aveo Sedan",
		],
		// Chery
		"76" => [
			"B12" => "B11",
		],
		// BMW
		"80" => [
			"1 серия 116"	=> "1 Серии",
			"3 серия 316"	=> "3 Серии",
			"3 серия 318"	=> "3 Серии",
			"3 серия 320"	=> "3 Серии",
			"3 серия 325"	=> "3 Серии",
			"5 серия 518"	=> "5 Серии",
			"5 серия 523"	=> "5 Серии",
			"5 серия 525"	=> "5 Серии",
			"5 серия 528"	=> "5 Серии",
			"5 серия 540"	=> "5 Серии",
			"7 серия"		=> "7 Серии",
			"7 серия 728"	=> "7 Серии",
			"7 серия 750"	=> "7 Серии",
		],
		// Bentley
		"81" => [
			"Continental" => "Continental GT",
		],
		// Audi
		"82" => [
			"A6 Allroad quattro" => "A6 allroad quattro",
		],
		// Lyxgen
		"57350" => [
			"8 SUV" => "7 SUV",
		],
		// Ford
		"6692" => [
			"F-151" => "F-150",
			"C-MAX" => "Focus C-MAX",
		],
		// Коммерческие автомобили
		// Baw
		"6689" => [
			"Tonic" => "Tonic 33463",
		],
		// Citroen
		"6690" => [
			"Berlingo"	=> "Berlingo фургон",
			"Nemo"		=> "Nemo",
			"C15"		=> "C15",
			"C25"		=> "C25",
			"C35"		=> "C35",
			"C8"		=> "C8",
			"Dispatch"	=> "Dispatch",
			"Evasion"	=> "Evasion",
			"H Van"		=> "H Van",
			"Relay"		=> "Relay",
			"Synergie"	=> "Synergie",
		],
		// Fiat
		"6691" => [
			"Doblo"		=> "Doblo Cargo",
			"Scudo"		=> "Scudo",
			"Florino"	=> "Florino",
			"Daily"		=> "Daily",
			"Pratico"	=> "Pratico",
			"Qubo"		=> "Qubo",
			"Strada"	=> "Strada",
			"Talento"	=> "Talento",
			"Ulysse"	=> "Ulysse",
		],
		// Ford
		"6692" => [
			"F-Series"			=> "F-Series",
			"Escort"			=> "Escort Express",
			"Tourneo Custom"	=> "Tourneo Custom",
			"Tourneo Connect"	=> "Tourneo Connect",
			"Transit Connect"	=> "Transit Connect",
			"Ranger"			=> "Ranger",
			"Aerostar"			=> "Aerostar",
			"Econoline"			=> "Econoline",
			"Excursion"			=> "Excursion",
			"Freda"				=> "Freda",
			"Lobo"				=> "Lobo",
			"Pampa"				=> "Pampa",
			"Windstar"			=> "Windstar",
		],
		// Foton
		"6693" => [
			"Aumark BJ 1039"	=> "Aumark бортовой",
			"Aumark BJ 1049"	=> "Aumark бортовой",
			"Ollin BJ 1039"		=> "Olin бортовой",
			"Ollin BJ 1041"		=> "Olin бортовой",
			"Sapu"				=> "Sapu",
			"Tunland C1"		=> "Tunland C1",
			"Tunland C2"		=> "Tunland C2",
			"Tunland C3"		=> "Tunland C3",
			"Tunland CX"		=> "Tunland CX",
			"Tunland L"			=> "Tunland L",
			"View C"			=> "View C",
			"View L"			=> "View L",
			"View M"			=> "View M",
			"View W"			=> "View W",
		],
		// Fuso
		"6694" => [],
		// Hino
		"6695" => [
			"300" => "300",
		],
		// Hyandai
		"6696" => [
			"Libero"		=> "Libero",
			"H200"			=> "H-200",
			"H-100"			=> "H-100",
			"H-1"			=> "H-1",
			"Porter"		=> "Porter",
			"Bakkie"		=> "Bakkie",
			"Grace"			=> "Grace",
			"H300"			=> "H300",
			"HR"			=> "HR",
			"i800"			=> "i800",
			"iLoad"			=> "iLoad",
			"iMax"			=> "iMax",
			"Libero"		=> "Libero",
			"Satellite"		=> "Satellite",
			"Shehzore"		=> "Shehzore",
			"Starex"		=> "Starex",
			"TQ"			=> "TQ",
		],
		// ISUZU
		"6697" => [
			"N-Series"	=> "NQR",
			"Midi"		=> "Midi",
			"Como"		=> "Como",
			"Campo"		=> "Campo",
			"D-Max"		=> "D-Max",
			"Fargo"		=> "Fargo",
			"Fuego"		=> "Fuego",
			"Hombre"	=> "Hombre",
			"i-Series"	=> "i-Series",
			"KB"		=> "KB",
			"Wasp"		=> "Wasp",
			"WFR"		=> "WFR",
		],
		// Iveco
		"6698" => [
			"Massif"	=> "Massif",
		],
		// KIA
		"53753" => [
			"Besta"		=> "Besta",
			"Bongo"		=> "Bongo",
			"Towner"	=> "Towner",
		],
		// Mercedes-Benz
		"6699" => [
			"V-Класс"	=> "V-Класс",
			"T2"		=> "T2",
			"T1"		=> "T1",
			"Vaneo"		=> "Vaneo",
			"Viano"		=> "Viano",
		],
		// Nissan
		"6700" => [
			"Vanette"		=> "Vanette",
			"Urvan"			=> "Urvan",
			"Serena"		=> "Serena",
			"Primastar"		=> "Primastar",
			"NV200"			=> "NV200",
			"Interstar"		=> "Interstar",
			"Homy"			=> "Homy",
			"Caravan"		=> "Caravan",
			"Atlas"			=> "Atlas",
			"NP300"			=> "NP-300",
			"Cabstar"		=> "Cabstar Double Cab",
			"Caball"		=> "Caball",
			"Clipper"		=> "Clipper",
			"Elgrand"		=> "Elgrand",
			"Hardbody"		=> "Hardbody",
			"Titan"			=> "Titan",
			"Hustler"		=> "Hustler",
			"Roox"			=> "Roox",
			"Junior"		=> "Junior",
			"Kubistar"		=> "Kubistar",
			"Largo"			=> "Largo",
			"NP200"			=> "NP200",
			"NV"			=> "NV",
			"NV350"			=> "NV350",
			"NV400"			=> "NV400",
			"Otti"			=> "Otti",
			"Pick UP"		=> "Pick UP",
		],
		// Peugeot
		"6701" => [
			"J5"		=> "J-Серия",
			"Expert"	=> "Expert",
			"Bipper"	=> "Bipper",
			"Partner"	=> "Partner Origin VU",
			"Boxer"		=> "Boxer фургон",
			"J7"		=> "J7",
			"Hoggar"	=> "Hoggar",
		],
		// Renault
		"6702" => [
			"Maxity"		=> "Maxity",
			"Mascott"		=> "Mascott",
			"Kangoo"		=> "Kangoo Fourgon",
			"Master"		=> "Master",
			"Trafic"		=> "Trafic Passenger",
			"Estafette"		=> "Estafette",
			"Express"		=> "Express",
		],
		// Skoda
		"6703" => [
			"Praktik" => "Praktik",
		],
		// Tata
		"6704" => [],
		// Toyta
		"6705" => [
			"Tundra"			=> "Tundra Pick-Up",
			"Town Ace"			=> "Town Ace",
			"Tacoma"			=> "Tacoma",
			"Model F"			=> "Model F",
			"Lite Ace"			=> "Lite Ace",
			"Hilux"				=> "Hilux",
			"Hiace"				=> "Hiace",
			"Estima"			=> "Estima",
			"Granvia"			=> "Granvia",
			"Master"			=> "Master",
			"MasterAce"			=> "MasterAce",
			"MiniAce"			=> "MiniAce",
			"Noah"				=> "Noah",
			"ProAce"			=> "ProAce",
			"Quantum"			=> "Quantum",
			"Regius"			=> "Regius",
			"Space Cruiser"		=> "Space Cruiser",
			"Sparky"			=> "Sparky",
			"Stallion"			=> "Stallion",
			"Stout"				=> "Stout",
			"T100"				=> "T100",
			"Tacoma"			=> "Tacoma",
			"Tamaraw FX"		=> "Tamaraw FX",
			"Unser"				=> "Unser",
			"Van"				=> "Van",
			"Vellfire"			=> "Vellfire",
			"Voxy"				=> "Voxy",
		],
		// Volkswagen
		"6706" => [
			"LT"			=> "LT",
			"Caravelle"		=> "Caravelle",
			"Touran"		=> "Touran",
			"Sharan"		=> "Sharan",
			"Amarok"		=> "Amarok",
			"California"	=> "California",
			"Multivan"		=> "Multivan",
			"L80"			=> "L80",
			"Saveiro"		=> "Saveiro",
			"Suran"			=> "Suran",
			"Taro"			=> "Taro",
		],
		// ВИС
		"6707" => [
			"2345" => "234500-30 (бортовой)",
			"1705" => "1705",
			"2347" => "2347",
		],
		// ГАЗ
		"6708" => [
			"3310 Валдай"	=> "3310",
			"Соболь 2310"	=> "2310",
			"2757"			=> "2757",
			"2834"			=> "2834",
			"2818"			=> "2818",
			"2704"			=> "2704",
			"33023"			=> "33023",
			"3221"			=> "3221 микроавтобус коммерческий",
			"3302"			=> "3302",
			"Соболь 2752"	=> "2752 фургон",
			"Соболь 2753"	=> "2752 комби",
			"Соболь 2310"	=> "Соболь - Бизнес борт-тент",
			"2308 Атаман"	=> "2308 Атаман",
			"51"			=> "51",
			"66"			=> "66",
			"67"			=> "67",
			"69"			=> "69",
			"2707"			=> "2707",
			"2742"			=> "2742",
			"3274"			=> "3274",
			"3796"			=> "3796",
		],
		// ТагАЗ
		"6709" => [
			"Master"	=> "LC100 (Master)",
			"Hardy"		=> "Hardy",
		],
		// Уаз
		"6710" => [
			"452"			=> "452",
			"452Д"			=> "452",
			"2360"			=> "2360",
			"39094"			=> "39094",
			"3303"			=> "3303",
			"2206"			=> "2206",
			"3909"			=> "3909",
			"39625"			=> "39625",
			"3741"			=> "3741",
			"23602 Cargo"	=> "23602 Cargo",
		],
	];

	public $steering_wheel_array = [
		'2' => 'левый',
		'1' => 'правый',
	];

	public $body_array = [
		'1'		=> 'седан',
		'2'		=> 'универсал',
		'3'		=> 'пикап',
		'4'		=> 'хэтчбек',
		'5'		=> 'кабриолет',
		'6'		=> 'купе',
		'7'		=> 'фургон',
		'8'		=> 'внедорожник',
		'9'		=> 'лимузин',
		'10'	=> 'минивэн',
		'12'	=> 'автобус',
	];

	public $transmission_array = [
		'1' => 'передний',
		'2' => 'задний',
		'3' => 'полный',
		'4' => 'полный подключаемый',
	];

	public $engine_type_array = [
		'1' => 'бензиновый',
		'2' => 'дизельный',
		'3' => 'бензиновый турбо',
		'4' => 'турбодизель',
		'5' => 'гибридный',
	];

	public $kpp_array = [
		'1' => 'механическая',
		'2' => 'автоматическая',
		'3' => 'вариатор',
		'5' => 'роботизированная',
	];

	public $state_array = [
		'1' => 'отличное',
		'2' => 'хорошее',
		'3' => 'среднее',
		'4' => 'битый',
	];

	public $custom_house_state = [
		'1' => 'растаможен',
		'2' => 'нерастаможен',
	];

	public $requiredParams = [
		"brand",
		"model",
		"year",
		"seller",
		"photo",
		"body",
		"transmission",
		"steering_wheel",
		"engine_type",
		"kpp",
		"engine_volume",
		"phone",
		"price",
		"distance",
		"region",
//		"text",
		"engine_power",
//		"color",
	];

	public $savingData = [
//        "original_url",
		"brand",
		"model",
		"year",
		"seller",
		"photo",
		"body",
		"transmission",
		"steering_wheel",
		"engine_type",
		"kpp",
		"engine_volume",
		"engine_power",
		"color",
		"owners",
		"state",
		"custom_house_state",
		"vin",
		"phone",
		"price",
		"distance",
		"text",
		"region",
		"inspection_plase",
	];
	
	/**
	 * Хранит в себе все марки легковых
	 * и коммерческих автомобилей
	 * 
	 * @var array $brands
	 */
	public static $brands = null;
		
	/**
	 * Хранит в себе все модели легковых
	 * и коммерческих автомобилей
	 * 
	 * @var array $models
	 */
	public static $models = null;
	
	/**
	 * Хранит в себе все ids легковых
	 * и коммерческих автомобилей
	 * 
	 * @var array $brandIds
	 */
	public static $brandIds = null;

	protected function advertDuplicateExists($advert)
	{
		$attributes = [
			"seller"			=> $advert["seller"],
			"brand"				=> $advert["brand"],
			"model"				=> $advert["model"],
			"year"				=> $advert["year"],
			"body"				=> $advert["body"],
			"transmission"		=> $advert["transmission"],
			"steering_wheel"	=> $advert["steering_wheel"],
			"engine_type"		=> $advert["engine_type"],
			"phone"				=> $advert["phone"],
			"price"				=> $advert["price"],
			"distance"			=> $advert["distance"],
			"hidden"			=> 0,
		];

		$models = AdvertCar::model()->findAllByAttributes($attributes);

		if (count($models) >= 1)
			return true;

		return false;
	}

	/**
	 * Возвращает массив со всеми марками
	 * легковых и коммерческих авто
	 * 
	 * @return array
	 */
	public static function getBrands()
	{
		if (!is_null(self::$brands))
			return self::$brands;
		
		self::$brands = Yii::app()
			->db
			->createCommand("SELECT id, name, category FROM brands WHERE category IN (1,2) ORDER BY name")
			->queryAll();
		
		return self::$brands;
	}

	/**
	 * Возвращает массив со всеми моделями
	 * легковых и коммерческих авто
	 * 
	 * @return array
	 */
	public static function getModels()
	{
		if (!is_null(self::$models))
			return self::$models;
		
		$brandIds = implode(",", self::getBrandIds());
		
		self::$models = Yii::app()
			->db
			->createCommand('SELECT id, name FROM models WHERE category IN (' . $brandIds . ') ORDER BY name')
			->queryAll();
		
		return self::$models;
	}
	
	/**
	 * Возвращает ids марок легковых и
	 * коммерческих автомобилей
	 * 
	 * @return array
	 */
	public static function getBrandIds()
	{
		if (!is_null(self::$brandIds))
			return self::$brandIds;
		
		$brands = self::getBrands();
		
		self::$brandIds = array();
		
		foreach ($brands as $brand)
		{
			if (!in_array($brand["id"], self::$brandIds))
				self::$brandIds[] = $brand["id"];
		}
		
		return self::$brandIds;
	}

	/**
	 * Валидация объявления
	 * 
	 * @param array $advert Объявление
	 * @return boolean
	 */
	protected function validateAdvert($advert)
	{
		foreach ($this->requiredParams as $requiredParam)
		{
			if (empty($advert[$requiredParam])
				&& (($advert[$requiredParam] != " ") || ($advert[$requiredParam] != 0)))
				return false;
		}

		return true;
	}

	/**
	 * Приводит параметр "дистанция" к нормальному виду
	 * 
	 * @param string $distance Дистанция
	 * @return string
	 */
	protected function formatDistance($distance)
	{
		$distance = str_replace(" ", "", $distance);
		preg_match_all('/[\d]+/', $distance, $matches);
		$distance = implode("", $matches[0]);

		return $distance;
	}

	/**
	 * Приводит параметр "цена" к нормальному виду
	 * 
	 * @param string $price Цена
	 * @return string
	 */
	protected function formatPrice($price)
	{
		$price = str_replace(" ", "", $price);

		return $price;
	}

	/**
	 * Приводит параметр "телефон" к нормальному виду
	 * 
	 * @param string $phone Телефон
	 * @return string
	 */
	protected function formatPhone($phone)
	{
		$phone = str_replace(" ", "", $phone);
		$phone = str_replace("Б", "6", $phone);

		if ((strlen($phone) > 0) && ($phone[0] == "("))
			$phone = "8" . $phone;

		$phone = str_replace("(", "-", $phone);
		$phone = str_replace(")", "-", $phone);

		return $phone;
	}

	/**
	 * Приводит параметр "регион" к нормальному виду
	 * 
	 * @param string $region Регион
	 * @return string
	 */
	protected function formatRegion($region)
	{
		$region = preg_replace("/\s\s+/", "", $region);

		return ucfirst(strtolower($region));
	}

	/**
	 * Приводит параметр "продавец" к нормальному виду 
	 * 
	 * @param string $seller Продавец
	 * @return string
	 */
	protected function formatSeller($seller)
	{
		$exceptionSeller = [
			'Автостайл',
			'АвтоСтайл',
			'БЦР Моторс', 
			'АвтоРегион НН',
			'Авто Лайф',
			'Global Cars',
		];

		$seller = preg_replace("/\s\s+/", "", $seller);
		$seller = str_replace("\n", "", $seller);
		$seller = trim($seller);

		if (in_array($seller, $exceptionSeller))
				return "";

		if (count(explode(" ", $seller)) > 1)
			return $seller;
		else
			return ucfirst(strtolower($seller));
	}

	/**
	 * Приводит параметр "цвет" к нормальному виду
	 * 
	 * @param string $color Цвет
	 * @return string
	 */
	protected function formatColor($color)
	{
		$color = str_replace("  ", "", $color);

		return strtolower($color);
	}

	/**
	 * Приводит параметр "трансмиссия" к формату<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $transmission Трансмиссия
	 * @return integer
	 */
	protected function formatTransmission($transmission)
	{
		foreach ($this->transmission_array as $key => $item)
		{
			$pattern = "/\b{$transmission}\b/iu";

			if (preg_match($pattern, $item))
				return $key;
		}
	}

	/**
	 * Приводит параметр "тип двигателя" к формату<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $engineType Тип двигателя
	 * @return integer
	 */
	protected function formatEngineType($engineType)
	{
		switch ($engineType)
		{
			case "Бензин":
				$engineType = 1;
				break;
			case "Дизель":
				$engineType = 2;
				break;
			case "Гибрид":
				$engineType = 5;
				break;
			default:
				$engineType = "";
				break;
		}

		return $engineType;
	}

	/**
	 * Приводт параметр "кпп" к формату<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $kpp КПП
	 * @return integer
	 */
	protected function formatKpp($kpp)
	{
		switch ($kpp)
		{
			case "Автомат":
				$kpp = 2;
				break;
			case "Механика":
				$kpp = 1;
				break;
			case "Робот":
				$kpp = 5;
				break;
			case "Вариатор":
				$kpp = 3;
				break;
			default:
				$kpp = "";
				break;
		}

		return $kpp;
	}

	/**
	 * Приводит параметр "состояние" к формату<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $state Состояние
	 * @return integer
	 */
	protected function formatState($state)
	{
		switch ($state)
		{
			case "Отличное состояние":
				$state = 1;
				break;
			case "Хорошее состояние":
				$state = 2;
				break;
			case "Среднее состояние":
				$state = 3;
				break;
			case "Битый":
				$state = 4;
				break;
			case "Новый":
				$state = 1;
				break;
			default:
				$state = "";
				break;
		}

		return $state;
	}

	/**
	 * Приводит параметр "тип кузова" к формату<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $body Тип кузова
	 * @return array mixed
	 */
	protected function formatBody($body)
	{
		$pattern = "/\b(седан|универсал|пикап|хетчбэк|кабриолет|купе|фургон|внедорожник|лимузин|минивэн|автобус|кроссовер|микроавтобус|лифтбэк|фастбэк)\b/iu";
		preg_match($pattern, $body, $matches);

		if (!isset($matches[0]))
			return "";
		
		$find = trim(strtolower($matches[0]));

		switch (strtolower($find))
		{
			case "седан":
			case "Седан":
				$body = 1;
				break;
			case "Универсал":
			case "универсал":
				$body = 2;
				break;
			case "пикап":
				$body = 3;
				break;
			case "Хетчбэк":
			case "хэтчбек":
			case "хэтчбэк":
			case "хетчбэк":
				$body = 4;
				break;
			case "Кабриолет":
			case "кабриолет":
				$body = 5;
				break;
			case "Купе":
			case "купе":
				$body = 6;
				break;
			case "Фургон":
			case "фургон":
				$body = 7;
				break;
			case "Внедорожник":
			case "внедорожник":
				$body = 8;
				break;
			case "Лимузин":
			case "лимузин":
				$body = 9;
				break;
			case "Минивэн":
			case "минивэн":
				$body = 10;
				break;
			case "Автобус":
			case "автобус":
				$body = 12;
				break;
			case "Кроссовер":
			case "кроссовер":
				$body = 8;
				break;
			case "Микроавтобус":
			case "микроавтобус":
				$body = 10;
				break;
			case "Лифтбэк":
			case "лифтбэк":
				$body = 4;
				break;
			case "Фастбэк":
			case "фастбэк":
				$body = 4;
				break;
			default:
				$body = "";
				break;
		}

		if (isset($this->body_array[$body]))
			return $body = [
				"id"	=> $body,
				"name"	=> $this->body_array[$body],
			];
	}

	/**
	 * Приводит параметр "таможня" к виду<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $customHouseState Таможня
	 * @return integer
	 */
	protected function formatCustomHouseState($customHouseState)
	{
		switch ($customHouseState)
		{
			case "растаможен":
				$customHouseState = 1;
				break;
			case "не растаможен":
				$customHouseState = 2;
				break;
			default:
				$customHouseState = "";
				break;
		}

		return $customHouseState;
	}

	/**
	 * Приводит параметр "тип руля" к виду<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param string $steeringWheel Тип руля
	 * @return integer
	 */
	protected function formatSteeringWheel($steeringWheel)
	{
		foreach ($this->steering_wheel_array as $key => $item)
		{
			$pattern = "/\b{$steeringWheel}\b/iu";

			if (preg_match($pattern, $item))
				return $key;
		}
	}

	/**
	 * Приводит параметр "фотография" к виду<br>
	 * пригодному для сохранения в базе
	 * 
	 * @param array $photos Фотография
	 * @return array mixed
	 */
	protected function formatPhoto($photos)
	{
		if (empty($photos))
			return;
		
		foreach ($photos as $key => $photo)
		{
			$photoPath = explode("/", $photo);
			$photoPath = "/files/tmp/" .$photoPath[0] . "/" . $photoPath[1];

			$photos[$key] = [
				"src" => $photoPath,
				"alt" => "",
			];
		}

		return $photos;
	}

	/**
	 * Возвращает массив id марок авто
	 * 
	 * @param string $brand Название марки из спарсенного объявления
	 * @return array $foundBrands Массив найденных марок
	 */
	protected function compareBrand($brand)
	{
		// исключения
		$exceptionBrands = [];

		$brand = explode(" ", $brand);
		$advertBrand = isset($brand[1]) ? $brand[1] : $brand[0];

		if (in_array($advertBrand, $exceptionBrands))
			return;

		$ourBrands = self::getBrands();
		$foundBrands = [];

		foreach ($ourBrands as $ourBrand)
		{
			$pattern = "/\b{$advertBrand}\b/i";

			if (preg_match($pattern, $ourBrand["name"]))
				$foundBrands[] = $ourBrand["id"];
		}
		
		return $foundBrands;
	}

	protected function compareModel(array $brands, $model, $body)
	{
		// из объявления
		$advertBrands = $brands;
		$advertModel = $model;

		// с drivenn
		$ourModels = self::getModels();

		foreach ($ourModels as $ourModel)
		{
			if ($ourModel["brand"] !== $advertBrands[0] || $ourModel["brand"] !== $advertBrands[1])
				return "";
			
			if (isset($this->tableConformity[$advertBrands[0]][$advertModel]))
					$advertModel = $this->tableConformity[$advertBrands[0]][$advertModel];
			else if (isset($this->tableConformity[$advertBrands[1]][$advertModel]))
				$advertModel = $this->tableConformity[$advertBrands[1]][$advertModel];
			
			$patternWithoutBody = "/^{$advertModel}$/iu";
			
			if (preg_match($patternWithoutBody, $ourModel["name"]))
				return $ourModel["id"];
			
			$patternWithBody = "/\b{$advertModel}\s+{$body}\b/iu";

			if (preg_match($patternWithBody, $ourModel["name"]))
				return $ourModel["id"];
		}
	}

	/**
	 * Приводит объявление к нормальному виду,
	 * который пригоден для записи в бд
	 * 
	 * @param array $rawAdvert Необработанное объявление
	 * @return array $advert
	 */
	protected function advertNormalization($rawAdvert)
	{
		// Тип кузова
		if (isset($rawAdvert["body"]))
		{
			$body = $this->formatBody($rawAdvert["body"]);
			$bodyName = !empty($body["name"]) ? $body["name"] : "";
			$advert["body"] = !empty($body["id"]) ? $body["id"]: "";
		}
		else
		{
			$advert["body"] = "";
			$bodyName = "";
		}

		// Марка
		$advert["brand"] = isset($rawAdvert["brand"]) ? $this->compareBrand($rawAdvert["brand"]) : "";
		
		// Модель
		$advert["model"] = isset($rawAdvert["model"]) 
				? $this->compareModel($advert["brand"], $rawAdvert["model"], $bodyName) 
				: "";
		
		// Год
		$advert["year"] = isset($rawAdvert["year"]) ? $rawAdvert["year"] : "";
		
		// Продавец
		$advert["seller"] = isset($rawAdvert["seller"]) ? $this->formatSeller($rawAdvert["seller"]) : "";
		
		// Фото
		$advert["photo"] = isset($rawAdvert["images"]) ? $this->formatPhoto($rawAdvert["images"]) : "";
		
		// Привод
		$advert["transmission"] = isset($rawAdvert["transmission"]) 
				? $this->formatTransmission($rawAdvert["transmission"]) 
				: "";
		
		// Руль
		$advert["steering_wheel"] = isset($rawAdvert["steering_wheel"]) 
				? $this->formatSteeringWheel($rawAdvert["steering_wheel"]) 
				: "";
		
		// Тип двигателя
		$advert["engine_type"] = isset($rawAdvert["engine_type"]) 
				? $this->formatEngineType($rawAdvert["engine_type"]) 
				: "";
		
		// Тип КПП
		$advert["kpp"] = isset($rawAdvert["kpp"]) ? $this->formatKpp($rawAdvert["kpp"]) : "";
		
		// Объём
		$advert["engine_volume"] = isset($rawAdvert["engine_volume"]) ? $rawAdvert["engine_volume"] : "";
		
		// Мощность
		$advert["engine_power"] = isset($rawAdvert["engine_power"]) ? $rawAdvert["engine_power"] : "";
		
		// Цвет
		$advert["color"] = isset($rawAdvert["color"]) ? $this->formatColor($rawAdvert["color"]) : "";
		
		// Текст объявления
		$advert["text"] = isset($rawAdvert["text"]) ? $rawAdvert["text"] : "";
		
		// Телефон
		$advert["phone"] = isset($rawAdvert["phone"]) ? $this->formatPhone($rawAdvert["phone"]) : "";
		
		// Цена
		$advert["price"] = isset($rawAdvert["price"]) ? $this->formatPrice($rawAdvert["price"]) : "";
		
		// Регион
		$advert["region"] = isset($rawAdvert["region"]) ? $this->formatRegion($rawAdvert["region"]) : "";
		
		// Место осмотра
		$advert["inspection_plase"] = isset($rawAdvert["inspection_place"]) ? $rawAdvert["inspection_place"] : "";
		
		// Хозяев в ПТС
		$advert["owners"] = isset($rawAdvert["owners"]) ? $rawAdvert["owners"] : "";
		
		// Состояние
		$advert["state"] = isset($rawAdvert["state"]) ? $this->formatState($rawAdvert["state"]) : "";
		
		// Таможка
		$advert["custom_house_state"] = isset($rawAdvert["custom_house_state"]) 
				? $this->formatCustomHouseState($rawAdvert["custom_house_state"]) : "";
		
		// VIN
		$advert["vin"] = isset($rawAdvert["vin"]) ? $rawAdvert["vin"] : "";
		
		// Пробег
		$advert["distance"] = isset($rawAdvert["distance"]) ? $this->formatDistance($rawAdvert["distance"]) : "";

		return $advert;
	}

	protected function advertSave($advert)
	{
		$messageStatus = ($advert->post_status == 'p') ? "Сохранено|Опубликовано" : "Сохранено|Модерация";
//		echo "{$messageStatus} \n";
		if($advert->save())
			echo "{$messageStatus} \n";
		else
		{
			echo $advert->scenario;
			print_r($advert->getErrors(), true);
		}
	}

	protected function advertUpdate($advert, $advertToSave)
	{
		echo "id = {$advert->id} ";

		$advert->price = $advertToSave["price"];
		$advert->distance = $advertToSave["distance"];
		$advert->phone = $advertToSave["phone"];

		if ($advert->scenario == 'parsing_advert')
			$advert->photo = $advertToSave["photo"];

		echo "Обновляем... ";

		$this->advertSave($advert);
	}

	/**
	 * Возвращает объект модели типа Advert
	 * 
	 * @param string $originalUrl URL объявления
	 * @param string $typeAdvert Тип объявления
	 * @return Advert
	 */
	protected function getExistAdvert($originalUrl, $typeAdvert)
	{
		$advert = $typeAdvert::model()->findByAttributes(['original_url' => $originalUrl, 'hidden' => 0]);

		return $advert;
	}

	/**
	 * Удаление неактуальных объявлений
	 * 
	 * @return boolean
	 */
	protected function removeIrrelevantAdverts()
	{
		$advertActualUrl = [];
		$collection = $this->_advertsActualCollection->find(["status" => "actual"]);

		foreach ($collection as $document)
		{
			$advertActualUrl[] = $document["advert_url"];
		}

		if (count($advertActualUrl) < 1)
			return false;

		$originalUrl = Yii::app()->db->createCommand()
			->select('id, original_url')
			->from('advert_car')
			->where('original_url <> " " AND hidden = 0')
			->queryAll();

		$count = 0;
		foreach ($originalUrl as $url)
		{
			if (!in_array($url["original_url"], $advertActualUrl))
			{
				$count++;
				echo "{$count} ";
				echo "Объявление с атрибутом original_url = {$url["original_url"]} не актуально \n";
				Yii::app()->db->createCommand()
					->update(
						'advert_car',
						['hidden' => 1,],
						'id=:id',
						[':id'=>$url["id"]]
					);
			}
		}
	}

	/**
	 * Добавляет новое объявление на сайт.<br>
	 * Если оно проходит валидацию, тогда публикуется<br>
	 * иначе отправляется на модерацию
	 * 
	 * @param array  $advert	Объявление
	 * @param string $advertUrl Url объявления
	 * @param string $hash		Хэш
	 * @return null
	 */
	protected function add($advert, $advertUrl, $hash)
	{
		$typeAdvert = $this->getAdvertType($advert);
		
		// Если объявление валидное и не существует дубликатов
		if ($this->validateAdvert($advert) == true
				&& !$this->advertDuplicateExists($advert))
		{
			echo 'Спарсенное объявление не найдено в числе собственных объявлений', "\n";

			$model = $this->getExistAdvert($advertUrl, $typeAdvert);
			
			if (!empty($model))
			{
				echo 'Обновляем объявление т.к. оно уже существует', "\n";

				$model->scenario = 'parsing_advert';
				$this->advertUpdate($model, $advert);
			}
			else
			{
				echo 'Записываем новое спарсенное объявление', "\n";

				$model = new $typeAdvert('parsing_advert');

				foreach ($this->savingData as $param)
				{
					$model->$param = $advert[$param];
				}

				$model->original_url = $advertUrl;
				$model->post_status = 'p';

				$this->advertSave($model);
			}
		}
		else 
		{
			// На модерацию даже не отправляем объявления
			// у которых нет продавца и фотографий
			if (empty($advert["photo"]) || empty($advert["seller"]))
				return false;

			if ($model = $this->getExistAdvert($advertUrl))
			{
				$model->scenario = 'parsing_advert_moderation';
				$this->advertUpdate($model, $advert);
			}
			else
			{
				$model = new $typeAdvert('parsing_advert_moderation');

				foreach ($this->savingData as $param)
				{
					$model->$param = $advert[$param];
				}

				$model->original_url = $advertUrl;

				$this->advertSave($model);
			}
		}

		$mongoData = [
			"advert_url"	=> $advertUrl,
			"advert"		=> json_encode($advert), 
			"hash"			=> $hash,
			"type"			=> $advert->getAdvertType($advert),
			"status"		=> "actual"
		];
		$this->_advertsActualCollection->save($mongoData);
	}

	/**
	 * Продляет объявление в случае если<br>
	 * до скрытия осталось меньше недели
	 * 
	 * @param string $date Дата
	 * @return boolean
	 */
	protected function deadLine($date)
	{
		$endDate = \DateTime::createFromFormat('d.m.Y', $date);
		$currentDate = \DateTime::createFromFormat('d.m.Y', date('d.m.Y'));
		$interval = $currentDate->diff($endDate);

		if ($interval->days < 7)
			return true;

		return false;
	}
	
	protected function updateActualCollection($advertUrl, $hash = null)
	{
		if (is_null($hash))
			$mongoData = ['$set' => ["status" => "actual"]];
		else
			$mongoData = ['$set' => ["hash" => $hash, "status" => "actual"]];
		
		$mongoCriteria = ["advert_url" => $advertUrl];
		$mongoModificator = ["upsert" => true];
		
		$this->_advertsActualCollection->update($mongoCriteria, $mongoData, $mongoModificator);
	}

	protected function update($actualAdvert, $advertUrl, $advertHash)
	{
		$model = $this->getModel($actualAdvert, $advertUrl);
		
		if ($model->post_status == "p")
		{
			if ($this->deadLine($model->obj_show_date) === true)
			{
				$model->obj_show_date = date('Y-m-d', strtotime('+30 day'));
				echo 'Продлеваем объявление id = ', $model->id, "\n";
			}
			
			$model->scenario = 'parsing_advert';
			$this->advertUpdate($model, $actualAdvert);
		}
		
		$this->updateActualCollection($advertUrl, $advertHash);
	}
	
	protected function save($actualAdvert, $advertUrl)
	{
		$model = $this->getModel($actualAdvert, $advertUrl);
		
		if ($model->post_status == "p"
				&& $this->deadLine($model->obj_show_date) === true)
		{
			$model->obj_show_date = date('Y-m-d', strtotime('+30 day'));

			echo 'Продлеваем объявление id = ', $model->id, "\n";
			
			$model->scenario = 'parsing_advert';
			$this->advertSave($model);
		}
		
		$this->updateActualCollection($advertUrl);
	}
	
	protected function getModel($actualAdvert, $advertUrl)
	{
		$typeAdvert = $this->getAdvertType($actualAdvert);
		$model = $typeAdvert::model()->findByAttributes(["original_url" => $advertUrl]);
		
		return $model;
	}
	
	/**
	 * Возвращает тип объявления из двух возможных вариантов<br>
	 * AdvertCar | CommercialCar
	 * 
	 * @param array mixed $advert Объявление
	 * @return string
	 */
	protected function getAdvertType($advert)
	{
		$brandId = $advert["type"];
		
		foreach (self::$brands as $brand)
		{
			if ($brand["id"] == $brandId)
				$typeId = $brand["category"];
		}
		
		if ($typeId == 1)
			$type = 'AdvertCar';
		else
			$type = 'AdvertCommercialCar';
		
		return $type;
	}


	public function init()
	{
		$mongoConnection = new MongoClient("mongodb://localhost");
		$mongoDb = $mongoConnection->selectDb("adverts");
		
		$this->_advertsActualCollection = new MongoCollection($mongoDb, "drivenn_adverts");
		$this->_advertsOriginalCollection = new MongoCollection($mongoDb, "am_ru_adverts");
		
		$this->_advertsActualCollection->update(
			[], 
			['$set' => [
				"status" => "not_actual"]
			], 
			["upsert" => false, "multiple" => true]
		);
	}

	public function run($args)
	{
		$documents = $this->_advertsOriginalCollection->find();

		foreach ($documents as $document)
		{
			if (empty($document["advert"]))
				continue;

			$advert     = json_decode($document["advert"], true);
			$advertUrl  = $document["advert_url"];
			$advertHash = $document["hash"];

			$actualAdvertCursor = $this->_advertsActualCollection->find(["advert_url" => $advertUrl])->limit(1);
			$actualAdvert = $actualAdvertCursor->current();
			
			// Если объявление не существует в базе
			if (empty($actualAdvert))
			{
				$actualAdvert = $this->advertNormalization($advert);
				$this->add($actualAdvert, $advertUrl, $advertHash);
			}
			
			// Если объявление существует, но хэш суммы не совпадают
			if (!empty($actualAdvert["hash"]) 
					&& ($actualAdvert["hash"] !== $advertHash))
			{
				$this->update($actualAdvert, $advertUrl, $advertHash);
			}
			else
				$this->save($actualAdvert, $advertUrl);
		}
		
		// Удаляем неактуальные объявления
		$this->removeIrrelevantAdverts();
	}

}
