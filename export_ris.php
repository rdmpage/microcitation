<?php

// Export reference(s) in RIS format

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

$field_to_ris_key = array(
	'title' 	=> 'TI',
	'journal' 	=> 'JO',
	'issn' 		=> 'SN',
	'volume' 	=> 'VL',
	'issue' 	=> 'IS',
	'spage' 	=> 'SP',
	'epage' 	=> 'EP',
	'year' 		=> 'Y1',
	'abstract'	=> 'N2',
	'url'		=> 'UR',
	'pdf'		=> 'L1',
	'doi'		=> 'DO'
	);

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$issn = '0085-4417';
//$issn = '0037-8941';
$issn = '0372-333X';
$issn = '0037-8941'; // Bulletin de la Société Botanique de France

$issn = '0006-8241'; // Bothalia

$issn = '0211-8327';

$issn = '0024-9637'; // Madroño

$issn = '0001-5709';

$issn = '1005-9628';

$issn = '0011-9970';

$issn = '1055-3177';

$issn = '0320-9180';

$issn = '1018-4171';

$issn = '0342-7536';

$issn = '2156-0382';
$issn = '0035-919X';

$issn = '0187-7151';

$issn = '1018-4171';
$issn = '0028-7199';
$issn = '0370-6583';
$issn = '0033-2615';
$issn = '0004-2625';

$issn = '0370-6583';

//$issn = '0187-7151';

$issn = '0013-8746';
$issn = '0084-5604';

$issn = '8756-971X';

$issn = '0372-333X';
$issn = '1253-8078';
$issn = '0374-5481';
$issn = '1560-7259';
$issn = '0451-9930';
$issn = '1808-2688';
$issn = '0260-1230';
$issn = '0025-1194';
$issn = '0005-6219';
$issn = '0031-0239';
$issn = '0375-0183';
$issn = '1805-5648';
$issn = '0043-5643';
$issn = '0022-1511';
$issn = '2156-0382';
$issn = '0187-7151';
$issn = '0033-2615'; // Psyche
$issn = '0028-7199';

$issn = '0323-6145'; // Berliner entomologische Zeitschrift

$issn = '0021-8375';
$issn = '1018-4171';

$issn = '0001-5326';
$issn = '0749-6737'; // Insecta Mundi
$issn = '0313-122X';

$issn = '1929-7890'; // Journal of The Entomological Society of British Columbia
$issn = '0071-0733'; // Journal of The Entomological Society of British Columbia
$issn = '0316-9049';
$issn = '0375-5223'; // Zeitschrift Der Arbeitsgemeinschaft Österreichischer Entomologen
$issn = '1001-4276'; // 武夷科学
$issn = '0506-7839'; // Vertebrata Hungarica
$issn = '0065-1710'; // Acta Zoologica Cracoviensia
$issn = '0072-9027'; // Gulf Research Reports
$issn = '0312-3162'; // records of the Western Australian Museum
//$issn = '1055-3177'; // Novon

$issn = '0026-6493'; // Annals of the Missouri Botanical Garden
$issn = '0312-3162'; // records of the Western Australian Museum

$issn = '0022-2062'; // Journal of Japanese botany
$issn = '1802-6842'; // Journal of The National Museum (Prague), Natural History Series

$issn = '0036-7575'; // Mitteilungen Der Schweizerischen Entomologischen Gesellschaft
$issn = '1815-8242';
$issn = '0161-8202'; // Journal of Arachnology


$issn = '0312-3162'; // records of the Western Australian Museum

//$issn = '0006-8071'; // Botanical Gazette

$issn = '0892-1016'; // Journal of Raptor Research
$issn = '0099-9059'; // Raptor Research
$issn = '0085-4417'; // Nuytsia
$issn = '0374-7859'; // Gardens' Bulletin Singapore

$issn = '0312-3162'; // records of the Western Australian Museum
$issn = '0002-8444'; // American Fern Journal

$issn = '0187-7151'; // Acta Botánica Mexicana

$issn = '0012-0073';
$issn = '0073-4705';

$issn = '1447-2546';

$issn = '0312-3162'; // records of the Western Australian Museum
$issn = '2278-1587'; // Indian Journal of Arachnology
$issn = '0024-9637'; // Madroño
$issn = '0312-3162'; // Records of the Western Australian Museum
//$issn = '0035-922X';

$issn = '0077-1813'; // Muelleria
$issn = '0365-7779'; // Anales de la Universidad de Chile

//$issn = '0250-4413'; // Entomofauna

$issn = '1559-4491'; // The Wilson Journal of Ornithology

$issn = '1018-4171';

$issn = '8756-971X';
$issn = '0044-586X';
$issn = '0340-4943';
$issn = '0740-2783';

$issn ='0312-3162';

$issn ='0034-740X';
$issn ='0312-3162';
$issn ='1684-4130';
$issn ='1815-8242';
$issn ='0077-1813';
$issn ='0811-3653';
$issn ='0077-1813';
$issn ='0187-7151';

$issn ='0379-0207';

$issn ='0311-9548';
$issn ='1447-2546'; // Memoirs of Museum Victoria
//$issn ='0814-1827'; // Memoirs of the Museum of Victoria
//$issn ='0083-5986';
//$issn ='0187-7151';

$issn = '0312-3162';
$issn = '0187-7151'; // Acta Botánica Mexicana
//$issn = '2304-7534';

//$issn = '1280-9659';

$issn ='1833-0290';

$issn = '0067-1975';
$issn = '0374-7859';
$issn = '0863-1867';
$issn = '0187-7151';
$issn = '0365-4508';
//$issn = '0374-7859';

$issn = '0068-547X';
$issn ='0365-4508';

$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';


$sql = 'SELECT * FROM publications WHERE doi="10.1636/0161-8202(2002)030[0219:tansgf]2.0.co;2"';
$sql = 'SELECT * FROM publications WHERE pdf="http://zoolstud.sinica.edu.tw/Journals/46.4/454.pdf"';
$sql = 'SELECT * FROM publications WHERE pdf="http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2056(2)/56rbz357-384.pdf"';
$sql = 'SELECT * FROM publications WHERE guid="http://www.zobodat.at/publikation_articles.php?id=237741"';

$sql = 'SELECT * FROM publications WHERE doi="10.24199/j.mmv.2014.72.07"';

$sql = 'SELECT * FROM publications WHERE doi="10.1206/3748.2"';

$sql = 'SELECT * FROM publications WHERE doi="10.3853/j.2201-4349.67.2015.1646"';

//$sql = 'SELECT * FROM publications WHERE journal="Malakozoologische Blätter"';

$sql = 'SELECT * FROM publications WHERE journal ="The Raffles Bulletin of Zoology"';

//$sql = 'SELECT * FROM publications WHERE journal ="Acta Societatis Zoologicae Bohemicae"';
//$sql .= ' AND year = 2005';

//$sql .= ' AND year = 2016';
//$sql .= ' AND year IN (2004, 2005, 2006)';
//$sql .= ' AND volume = 23 AND ISSUE IS NOT NULL';

//$sql .= ' AND year < 1901 and volume <> 0 AND spage is not null';

$sql = 'SELECT * FROM publications WHERE issn="' . $issn . '"';

$sql .= ' AND volume IN(64,46,48)';

//$sql = 'SELECT * FROM publications where journal like "Studies and%"';

//$sql .= ' AND year >= 1970';

//$sql .= ' AND year BETWEEN 1920 AND 1929';
//$sql .= ' AND volume = 11';

//$sql .= ' and spage <> 0';

//$sql .= ' AND doi IS NULL and year > 2011';

$sql = 'SELECT * FROM publications WHERE guid="https://florabase.dpaw.wa.gov.au/nuytsia/article/795"';
$sql = 'SELECT * FROM publications WHERE guid="10.15553/c2014v691a5"';
$sql = 'SELECT * FROM publications WHERE guid="http://researcharchive.calacademy.org/research/scipubs/pdfs/v58/proccas_v58_n08.pdf"';
$sql = 'SELECT * FROM publications WHERE guid="http://ejournal.sinica.edu.tw/bbas/content/2012/3/Bot533-14/Bot533-14.html"';
$sql = 'SELECT * FROM publications WHERE guid="http://journal.upao.edu.pe/Arnaldoa/article/view/683"';
$sql = 'SELECT * FROM publications WHERE guid="10.15517/lank.v17i1.28479"';

$sql = 'SELECT * FROM publications WHERE guid="10.6165/tai.2015.60.39"';

$sql = 'SELECT * FROM publications WHERE guid="10.7751/telopea8469"';

$sql = 'SELECT * FROM publications WHERE guid="https://www.rbg.vic.gov.au/documents/MuelleriaVol_34_-_p47-54_Molyneux.pdf"';
$sql = 'SELECT * FROM publications WHERE guid="https://www.qld.gov.au/dsiti/assets/documents/austrobaileya/bean-new-solanum-species-png-austrobaileya-v9s4-p560-599.pdf"';

$sql = 'SELECT * FROM publications WHERE guid="10.20531/tfb.2016.44.2.08"';

$sql = 'SELECT * FROM publications WHERE guid="https://lkcnhm.nus.edu.sg/app/uploads/2017/06/63rbz448-453.pdf"';

// Archivos do Museu Nacional do Rio de Janeiro
$sql = 'SELECT * FROM publications WHERE issn="0365-4508" AND volume=33';

$sql = 'SELECT * FROM publications WHERE guid="10.5735/085.047.0307"';


$sql = 'SELECT * FROM publications WHERE issn="1934-5259" AND volume=8';

$sql = "select * from publications where issn='0217-2445' and pdf is not null and internetarchive is null";

$sql = 'SELECT * FROM publications WHERE guid="http://www.repository.naturalis.nl/record/525521"';

$sql = 'SELECT * FROM publications WHERE guid="ZOOREC:ZOOR14703021123"';

$sql = 'SELECT * FROM publications WHERE issn="0149-175X" AND year > 2010';

$sql = 'SELECT * FROM publications WHERE issn="0033-2615" AND year In (1979, 1980)';

$sql = 'SELECT * FROM publications WHERE issn="0024-9637" AND year > 2012';


$sql = 'SELECT * FROM publications WHERE issn="0068-547X" AND volume="56, Supplement I"';


$sql = 'SELECT * FROM publications WHERE issn="0312-9764" AND volume="9" AND issue="4"';

$sql = "SELECT * FROM `publications` where (`publications`.`issn` IN ('0083-7903','1174-0043'))";

// RIS with PDFs for upload to IA
$sql = 'SELECT * FROM publications WHERE issn="0030-8714" AND pdf IS NOT NULL';
$sql = 'SELECT * FROM publications WHERE issn="2278-1587" AND pdf IS NOT NULL';
$sql = 'SELECT * FROM publications WHERE issn="0867-1710" AND pdf IS NOT NULL'; // Genus
$sql = 'SELECT * from publications where issn="0867-1710" and pdf is not null and internetarchive=""'; // genus extra
$sql = 'SELECT * FROM publications WHERE issn="2278-1587" AND pdf  LIKE "%.pdf.pdf"';

$sql = 'SELECT * from publications INNER JOIN sha1 USING (pdf) where issn="0024-1652" AND internetarchive IS NULL';

$sql = 'SELECT * from publications INNER JOIN sha1 USING (pdf) where issn="0166-5189" AND internetarchive IS NULL';

$sql = 'SELECT * from publications  where issn="0005-6219" AND internetarchive IS NULL';

$sql = 'SELECT * FROM publications  WHERE issn="0217-2445" AND volume LIKE "Supplement%" AND pdf IS NOT NULL AND spage IS NOT NULL';


//$sql .= ' AND year="2004"';

/*
$sql = 'SELECT  title, authors, journal, issn, series, volume, issue, spage, epage, year, doi, handle, url, pdf, sha1, updated 
FROM publications INNER JOIN sha1 USING(pdf) 
WHERE journal="Bulletin of the Osaka Museum of Natural History"';
*/

//$sql = 'SELECT * FROM publications WHERE issn="0370-047X" AND guid LIKE "http://search.informit.com.au%"';
//$sql = 'SELECT * FROM publications WHERE issn="0370-047X" AND volume="133"';

//$sql = 'SELECT * FROM publications WHERE issn="0370-047X" AND volume IN (96, 97,98,99,100,101)';


$sql = 'SELECT * FROM publications WHERE issn="0031-1847"';

$sql = 'SELECT * FROM publications WHERE issn="0035-9211" AND volume=103';

// 0300-5488
// 0370-7504
$sql = 'SELECT * FROM publications WHERE issn="0370-7504"';

$sql = 'SELECT * FROM publications WHERE pdf In ("http://revistas.unne.edu.ar/index.php/bon/article/download/1438/1209","http://revistas.unne.edu.ar/index.php/bon/article/download/1251/1034")';

$sql = 'SELECT * FROM publications WHERE issn="1853-8460" AND volume="13"';

$sql = 'SELECT * FROM publications WHERE issn="0375-0183"';

// Taiwania
$sql = 'SELECT * FROM publications WHERE issn="0372-333X" AND spage IS NOT NULL AND volume IS NOT NULL and pdf IS NOT NULL';

// -- 0495-3843
$sql = 'SELECT * FROM publications WHERE issn="0495-3843" AND spage IS NOT NULL AND volume IS NOT NULL and pdf IS NOT NULL';

//$sql .= ' AND volume IN (50,51)';


//$sql = 'SELECT * FROM publications WHERE issn="1447-2546" AND volume IN (68, 69,70,71)';

// bulletin of botanical research harbin 植物研究
$sql = 'SELECT * from publications INNER JOIN sha1 USING (pdf) where issn="1673-5102" AND internetarchive IS NULL AND spage IS NOT NULL AND volume IS NOT NULL and pdf IS NOT NULL';

//$sql .= ' AND pdf="http://bbr.nefu.edu.cn/CN/article/downloadArticleFile.do?attachType=PDF&id=3754"';

$sql = 'SELECT * FROM publications WHERE issn="0006-5196" AND spage IS NOT NULL AND volume IS NOT NULL and pdf IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="0035-922X" AND volume < 78';

$sql = 'SELECT * FROM publications WHERE issn="0161-8202" AND volume = 45';



$sql = 'SELECT * FROM publications WHERE issn="0971-2313" AND spage IS NOT NULL AND volume IS NOT NULL and pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0080-9462" and pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0077-1813" and year=2004';

$sql = 'SELECT * FROM publications WHERE issn="0385-2423" and pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0077-1813" and volume IN (21, 22,23,24)';

$sql = 'SELECT * FROM publications WHERE issn="0077-1813" and volume IN (26,28,29)';

//$sql = 'SELECT * FROM publications WHERE issn="0038-3872"';

$sql = 'select * from publications where issn="0778-9386" and volume=33';

$sql = 'SELECT * FROM publications WHERE issn="0035-9181"';

$sql = 'SELECT * FROM publications WHERE issn="0375-099X"';

$sql = 'SELECT * FROM publications WHERE issn="1176-6166" and pdf is not null AND spage IS NOT NULL AND (authors IS NOT NULL OR title LIKE "%obituary%")';


$sql = 'SELECT * FROM publications WHERE issn="0077-1813" and volume IN (31,32)';

$sql = 'SELECT * FROM publications WHERE issn="0068-547X" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0524-4994" AND pdf IS NOT NULL';

$sql = 'select * from publications where issn = "0068-547X" and internetarchive is not null and internetarchive like "%,%"';

$sql = 'SELECT * FROM publications WHERE issn="0067-4745" AND pdf IS NOT NULL';
$sql = 'SELECT * FROM publications WHERE issn="0067-8546" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="1026-3632" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0312-3162" and volume=24 and guid like "10%"';

$sql = 'SELECT * FROM publications WHERE guid IN (
"10.18195/issn.0312-3162.24(1).2007.065-079",
"http://www.museum.wa.gov.au/sites/default/files/A NEW GENUS AND SPECIES OF STYGOBITIC PARAMELITID AMPHIPOD FROM THE PILBARA, WESTERN AUSTRALIA.pdf",
"http://www.museum.wa.gov.au/sites/default/files/A NEW SPECIES OF THE FRESHWATER TANAIDACEAN GENUS PSEUDOHALMYRAPSEUDES (CRUSTACEA TANAIDACEA PARAPSEUDIDAE) FROM SULAWESI.pdf",
"http://www.museum.wa.gov.au/sites/default/files/A NEW SPECIES OF THE GENUS CANDONOPSIS (CRUSTACEA, OSTRACODA) FROM WESTERN AUSTRALIA.pdf",
"http://www.museum.wa.gov.au/sites/default/files/A NEW SPIDER WASP FROM WESTERN AUSTRALIA, WITH A DESCRIPTION OF THE FIRST KNOWN MALE OF THE GENUS EREMOCURGUS (HYMENOPTERA .pdf",
"http://www.museum.wa.gov.au/sites/default/files/CALLUCINA AND PSEUDOLUCINISCA (MOLLUSCA BIVALVIA LUCINIDAE) FROM A AUSTRALIA REVISION OF GENERAAND DESCRIPTION OF THREE NEW.pdf",
"http://www.museum.wa.gov.au/sites/default/files/GUNAWARDENEA, NEW GENUS OF SQUAT LEAFHOPPERS FROM WESTERN AUSTRALIA, WITH DESCRIPTION OF TWO NEW SPECIES (HEMIPTERA CICADEL.pdf",
"http://www.museum.wa.gov.au/sites/default/files/PHREODRILIDAE (CLITELLATA ANNELIDA) IN NORTH-WESTERN AUSTRALIA WITH DESCRIPTIONS OF TWO NEW SPECIES.pdf",
"http://www.museum.wa.gov.au/sites/default/files/SPECIES OF THJE SPONGE GENUS CHONDRILLA (DEMOSPONGIAE CHONDROSIDA CHONDRILLIDAE) IN AUSTRALIA.pdf"

)';


$sql = 'SELECT * FROM publications WHERE issn="0252-192X" AND pdf IS NOT NULL';


// Melbourne Museum journals
$sql = 'SELECT * FROM publications WHERE issn IN ("1447-2546", "0814-1827", "0083-5986", "0311-9548") AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0031-1847" AND pdf IS NOT NULL AND spage IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0387-5733" AND pdf IS NOT NULL AND spage IS NOT NULL AND guid LIKE "http://%"';



$sql = 'SELECT * FROM publications WHERE issn="0155-4131" AND pdf IS NOT NULL AND spage IS NOT NULL';

$sql = 'SELECT * FROM `publications` WHERE issn="1576-9518" AND pdf IS NOT NULL AND spage IS NOT NULL';

$sql = 'select * from publications where issn="0385-2423" and pdf is not null and internetarchive is null';



$sql = 'select * from publications where issn="0034-365X" and pdf is not null';

//$sql = 'select * from publications where guid IN ("2246/1634")';

//$sql = 'select * from publications where issn="0001-804X" and volume=4 and issue=2';

$sql = 'select * from publications where issn="0034-365X" and 
(pdf like "%88/91"
or pdf like "%87/90"
or pdf like "%86/89"
or pdf like "%85/88"
or pdf like "%85/88"
or pdf like "%84/87"
or pdf like "%83/86"
or pdf like "%78/81"
or pdf like "%76/79"
or pdf like "%50/54")';

$sql = 'select * from publications where issn="0034-365X" and 
(pdf like "%268/805"
or pdf like "%245/663")'
;


$sql = 'select * from publications where issn="2337-8824" and internetarchive is null';


$sql = 'select * from publications where issn="0723-4244" and pdf is not null';

// 0001-804X
$sql = 'select * from publications where issn="1872-9231"';

$sql = 'SELECT * FROM publications where issn="2357-3759" and pdf is not null';

$sql = 'select * from publications where pdf="http://hbs.bishopmuseum.org/pubs-online/pdf/iom8-2p13-33.pdf"';

$sql = 'select * from publications where journal="The Bulletin of The Raffles Museum"';


$sql = 'select * from publications where issn="0022-2062" and volume is not null and spage is not null and pdf LIKE "http://www.jjbotany.com/getpdf.php?tid%"';

//$sql .= ' AND updated > "2020-06-03"';


$sql = 'SELECT * FROM publications where issn="0368-0177" and pdf is not null and spage is not null';


$sql = 'select * from publications where journal="Abhandlungen Aus Dem Gebiete Der Naturwissenschaften Hamburg" and pdf is not null';

//$sql = 'SELECT * FROM publications where guid="http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/77-sup-2-2007"';

//$sql = 'SELECT * FROM publications where guid IN ("http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/48-1972/biologie-1972-48-2-_1-7.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/48-1972/biologie-1972-48-11-_1-7.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/48-1972/biologie-1972-48-12-_1-7.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/48-1972/biologie-1972-48-4-_1-14.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/48-1972/biologie-1972-48-9-_1-14.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/49-1973/biologie-1973-49-3-_1-13.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/49-1973/biologie-1973-49-9-_1-13.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/49-1973/biologie-1973-49-13-_1-13.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/49-1973/biologie-1973-49-5-_1-10.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/49-1973/biologie-1973-49-12-_1-10.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/50-1974/biologie-1974-50-4-_1-9.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/50-1974/biologie-1974-50-7-_1-9.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/51-1975-1979/biologie-1975-1979-51-1-_1-6.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/51-1975-1979/biologie-1975-1979-51-3-_1-5.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/51-1975-1979/biologie-1975-1979-51-5-_1-14.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/51-1975-1979/biologie-1975-1979-51-9-_1-14.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-3-_1-3.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-8-_1-3.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-9-_1-9.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-19-_1-9.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-14-_1-8.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-18-_1-8.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-20-_1-8.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-15-_1-12.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/52-1980/biologie-1980-52-16-_1-12.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/53-1981/biologie-1981-53-5-_1-3.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/53-1981/biologie-1981-53-11-_1-3.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/53-1981/biologie-1981-53-7-_1-14.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/53-1981/biologie-1981-53-12-_1-14.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/53-1981/biologie-1981-53-10-_1-4.pdf","http://biblio.naturalsciences.be/rbins-publications/bulletin-of-the-royal-belgian-institute-of-natural-sciences-biologie/53-1981/biologie-1981-53-15-_1-4.pdf")';

$sql = 'SELECT * FROM publications where issn="0001-804X" and volume=6';


$sql = 'SELECT * FROM publications WHERE issn="0155-4131" AND spage IS NOT NULL AND volume > 4';


$sql = 'SELECT * FROM publications where issn="0723-9912" and pdf is not null and spage is not null';


$sql = 'SELECT * FROM publications where issn = "0085-4417" and pdf is not null and spage is not null';

$sql = 'SELECT * FROM publications WHERE issn="0037-2870" and pdf is not null and spage is not null';

$sql = 'SELECT * FROM publications WHERE issn="0001-5202" and pdf is not null and spage is not null';

$sql = 'SELECT * FROM publications WHERE journal="Insects of Hawaii"';

$sql = 'SELECT * FROM publications where issn="0079-8835" and volume=60 and spage IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0723-9319"';

$sql = 'SELECT * FROM publications_amnh WHERE guid="2246/1135"';

$sql = 'SELECT * FROM publications WHERE issn="0312-9764" AND year IN (2012, 2013,2014)';

$sql = 'SELECT * FROM publications WHERE issn="2373-0951"';

$sql = 'SELECT * FROM publications WHERE issn="0312-9764"';

$sql = 'SELECT * FROM publications WHERE issn="0027-4100" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications where issn="0079-8835" and volume=59 and spage IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="0085-4417" and year > 2013';

$sql = 'SELECT * FROM publications WHERE issn="0003-4983"';

$sql = 'SELECT * FROM publications WHERE issn="0080-4274" AND volume=33';

$sql .= ' AND pdf IS NOT NULL AND spage IS NOT NULL';


// Get PMID
$sql = 'SELECT * FROM publications WHERE issn="0003-4150" AND year >= 1945';

$sql = 'SELECT * FROM publications WHERE issn="0042-8752" AND zoobank="x"';


$sql = 'SELECT * FROM publications WHERE issn="0041-1752" AND guid LIKE "10520%" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications_tmp WHERE issn="2096-2703" AND pdf IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="1005-3395" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="1346-7565" AND pdf IS NOT NULL AND guid LIKE "10%"';


$sql = 'SELECT * FROM publications WHERE issn="1674-4918" AND pdf LIKE "http://www.plantsystematics.com/CN/article/%"';

// Notes From The Royal Botanic Garden Edinburgh 
$sql = 'SELECT * FROM publications WHERE issn="0080-4274" AND  volume=30 and issue=1';

$sql = 'SELECT * FROM publications WHERE issn="1808-2688" AND pdf IS NOT NULL ';

// Trying to fix Chinese PDFs
$sql = 'SELECT * FROM publications WHERE issn="1674-4918" AND pdf IS NOT NULL AND pii="badpdf"';
$sql = 'SELECT * FROM publications WHERE issn="1005-3395" AND pdf IS NOT NULL AND pii="badpdf"';


$sql = 'SELECT * FROM publications WHERE issn="0067-0464" and volume=1 and spage is not null';

// Sichuan Journal of Zoology
$sql = 'SELECT * FROM publications WHERE guid="http://www.scdwzz.com/viewmulu_en.aspx?qi_id=117&mid=3905"';


$sql = 'SELECT * FROM publications WHERE issn="1000-7083" and pdf is not null';

$sql = 'SELECT * FROM publications WHERE issn="0080-4274" and pdf is not null';

$sql = 'SELECT * FROM publications_tmp WHERE issn="1409-3871"';

$sql = 'SELECT * FROM publications_tmp WHERE issn="1409-3871" and doi is null ';

$sql = 'SELECT * FROM publications WHERE issn="0024-0672" and volume="80" and spage="87"';

$sql = 'SELECT * FROM publications WHERE guid like "https://repository.naturalis.nl/pub/%" AND internetarchive IS NULL and issn="0024-0672" and pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0128-5939"';

$sql = 'SELECT * FROM publications WHERE issn="2033-494X"';

$sql = 'SELECT * FROM publications WHERE issn="0722-3773"';

$sql = 'SELECT * FROM publications WHERE issn="0171-0079" and pdf is not null';


// plant diversity

$sql = 'SELECT * FROM `publications_tmp` WHERE issn="2096-2703" AND volume BETWEEN 33 AND 37';


// PD Acta Yunnanica
$no_pages = true;
$no_pages = false;

$sql = 'SELECT * FROM `publications_tmp` WHERE issn="2096-2703" AND volume BETWEEN 27 AND 32 AND pii IS NOT NULL';

$sql = 'SELECT * FROM `publications_tmp` WHERE issn="2096-2703" AND volume < 24 AND year < 1999 AND pii IS NOT NULL';

$sql = 'SELECT * FROM `publications` WHERE issn="0726-9609" AND volume =29';

// Zoological Research
$sql = 'SELECT * FROM `publications` WHERE issn="2095-8137" AND pii IS NOT NULL';



$sql = 'SELECT * FROM publications WHERE issn="2337-876X" and pdf is not null and doi IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="1851-7471" and pdf is not null';


// PD Acta Yunnanica
$sql = 'SELECT * FROM `publications_tmp` WHERE issn="2096-2703" AND volume in (21,22,23,24,25,26) AND  pii IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="0079-0354" and pdf is not null';



$sql = 'SELECT * FROM publications WHERE issn="0770-7622" and pdf is not null and issue LIKE "%(%"';

$sql = 'SELECT * FROM publications_tmp WHERE issn="0968-0462"';

$sql = 'SELECT * FROM publications_tmp WHERE issn="0968-0446"';

$sql = 'SELECT * FROM publications WHERE issn="0024-0974" and pdf IS NOT NULL';
$sql = 'SELECT * FROM publications WHERE journal="Konowia (Vienna)" and pdf IS NOT NULL';

$sql = 'SELECT * FROM publications WHERE issn="0073-134X" and pdf IS NOT NULL AND volume IS NOT NULL AND spage IS NOT NULL';
//$sql = 'SELECT * FROM publications WHERE issn="0073-134X" and pdf IS NOT NULL AND pdf like "%\%%" AND volume IS NOT NULL AND spage IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="0253-116X" and pdf IS NOT NULL';

$sql = 'SELECT * FROM publications_tmp where issn="2410-0226" and pdf IS NOT NULL AND volume IS NOT NULL AND spage IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="2539-200X" and pdf IS NOT NULL and internetarchive IS NULL';

$sql = 'SELECT * FROM publications WHERE pdf="http://revistas.humboldt.org.co/index.php/biota/article/download/718/579"';

$sql = 'SELECT * FROM publications_tmp WHERE pdf="http://www.insect.org.cn/CN/article/downloadArticleFile.do?attachType=PDF&id=520"';

$sql = 'SELECT * FROM publications_tmp WHERE pdf="http://www.insect.org.cn/CN/article/downloadArticleFile.do?attachType=PDF&id=1854"';


$sql = 'SELECT * FROM publications WHERE issn="0385-2423" and pdf is not null and internetarchive is null';

$sql = 'SELECT * FROM publications WHERE issn="0385-2423" and pdf is not null and internetarchive is null';

$sql = "select * from publications where issn='0387-5733' and wikidata is null and pdf like 'http://bionames.org/%'";

// Bol Chile

//$sql = 'SELECT * FROM publications WHERE issn="0716-2545" and pdf is not null';



$sql = 'SELECT * FROM `publications_tmp` WHERE guid="http://www.insect.org.cn/CN/abstract/abstract5643.shtml"';
$sql = 'SELECT * FROM `publications_tmp` WHERE issn="0454-6296" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM `publications` WHERE issn="0006-8152" AND guid LIKE "https://www.zobodat.at%" AND pdf IS NOT NULL';

$sql = 'SELECT * FROM `journal of the malacological society of australia-wd`';

//$sql = 'SELECT * FROM publications WHERE issn IN("0097-0425","1050-4842")';


//$sql = 'SELECT * FROM publications WHERE guid="2246/1135"';

//$sql = 'SELECT * FROM publications WHERE issn="0312-3162" and year =2008 and biostor is null';

$sql = 'SELECT * FROM publications WHERE issn="1641-8190" and pdf is not null';

$sql = "SELECT * FROM publications WHERE doi in ('10.1515/pbj-2016-0021','10.1515/pbj-2016-0020','10.1515/pbj-2016-0012','10.1515/pbj-2016-0023','10.1515/pbj-2016-0013','10.1515/pbj-2016-0011','10.2478/pbj-2014-0015')";


$sql = 'SELECT * FROM publications_tmp WHERE issn="0206-0477" and pdf is not null';

$sql = 'SELECT * FROM publications WHERE issn="0368-1254" and pdf is not null';

$sql = 'SELECT * FROM publications WHERE issn="1978-9807" and pdf is not null and volume is not null and spage is not null';

$sql = 'SELECT * FROM publications WHERE issn="0013-886X"';

$sql = 'SELECT * FROM publications_tmp WHERE issn="0037-928X" AND pdf IS NOT NULL and guid like "https://lasef.org/wp-content/uploads/%"';

$sql = 'SELECT * FROM publications WHERE issn="1908-6865" AND pdf IS NOT NULL and volume=14';
$sql = 'SELECT * FROM publications WHERE issn="1908-6865" AND pdf IS NOT NULL';


$sql = 'SELECT * FROM publications WHERE issn="0013-9440" AND pdf IS NOT NULL';
$sql = 'SELECT * FROM publications WHERE issn="2444-8192" AND pdf IS NOT NULL';

$sql = "SELECT * FROM publications WHERE issn='2444-8192' AND pdf IN ('https://raco.cat/index.php/BolletiSHNBalears/article/download/208529/277716','https://raco.cat/index.php/BolletiSHNBalears/article/download/264180/351833','https://raco.cat/index.php/BolletiSHNBalears/article/download/264181/351834','https://raco.cat/index.php/BolletiSHNBalears/article/download/264182/351835','https://raco.cat/index.php/BolletiSHNBalears/article/download/264183/351836','https://raco.cat/index.php/BolletiSHNBalears/article/download/264184/351837','https://raco.cat/index.php/BolletiSHNBalears/article/download/264185/351838','https://raco.cat/index.php/BolletiSHNBalears/article/download/264186/351839','https://raco.cat/index.php/BolletiSHNBalears/article/download/264187/351840','https://raco.cat/index.php/BolletiSHNBalears/article/download/264188/351841','https://raco.cat/index.php/BolletiSHNBalears/article/download/264189/351842','https://raco.cat/index.php/BolletiSHNBalears/article/download/264190/351843','https://raco.cat/index.php/BolletiSHNBalears/article/download/264191/351844','https://raco.cat/index.php/BolletiSHNBalears/article/download/264192/351845','https://raco.cat/index.php/BolletiSHNBalears/article/download/264193/351846','https://raco.cat/index.php/BolletiSHNBalears/article/download/286548/374687','https://raco.cat/index.php/BolletiSHNBalears/article/download/286549/374688','https://raco.cat/index.php/BolletiSHNBalears/article/download/286550/374689','https://raco.cat/index.php/BolletiSHNBalears/article/download/286551/374690','https://raco.cat/index.php/BolletiSHNBalears/article/download/286552/374691','https://raco.cat/index.php/BolletiSHNBalears/article/download/286553/374692','https://raco.cat/index.php/BolletiSHNBalears/article/download/286554/374693','https://raco.cat/index.php/BolletiSHNBalears/article/download/286555/374694','https://raco.cat/index.php/BolletiSHNBalears/article/download/286556/374695','https://raco.cat/index.php/BolletiSHNBalears/article/download/286557/374696','https://raco.cat/index.php/BolletiSHNBalears/article/download/286559/374698','https://raco.cat/index.php/BolletiSHNBalears/article/download/286560/374699','https://raco.cat/index.php/BolletiSHNBalears/article/download/385137/478238')";

$sql = "SELECT * FROM publications WHERE issn='0037-8844' AND volume = 141";

$sql = "SELECT * FROM publications WHERE issn='1179-7193'";

$sql = "SELECT * FROM publications WHERE issn='0082-3074'";

$sql = "SELECT * FROM publications WHERE issn='1323-5818' and volume BETWEEN 15 AND 21";
$sql = "SELECT * FROM publications WHERE issn='0311-1881' and volume BETWEEN 1 AND 6";

$sql = "select * from publications where issn='0311-1881' and volume between 1 and 6 and epage is not null and biostor is null";

$sql = "select * from publications where issn='0034-7108' and volume is not null and spage is not null and year is not null and doi is null";

$sql = "select * from publications where issn='1474-0036' and pdf IS NOT NULL";

$sql = "select * from publications where issn='2269-6016' and guid like 'http://zoo%'";

$sql = "select * from publications where issn='1026-051X' and doi is not null and pdf is not null";

$sql = "select * from publications where issn='1026-051X' and CAST(volume AS SIGNED) < 337 and pdf is not null";

$sql = "select * from publications_mnhn where issn='0181-0626' and volume=12 and issue=1 and biostor is null";

$sql = "select * from publications_tmp where issn='0027-4070' and volume = 13 and biostor is NULL";


$sql = 'select * from `mammalian species-wd` where pdf like "https://www.science.smith.edu%" and wikidata is not null and NOT (doi IN ("10.2307/0.632.1","10.1644/0.692.1")) order by year, cast(volume as signed), cast(issue as signed)';


$sql = "select * from publications where issn='0311-1881' and volume in (7,8)";

$sql = "select * from publications where issn='0022-4324' and volume in (27,28) and biostor is null and authors is not null";

$sql = "select * from publications where issn='2007-9133' and pdf is not null";

// Current Science
$sql = "select * from publications where issn='0011-3891' and pdf is not null and guid='https://wwwops.currentscience.ac.in/Downloads/article_id_093_10_1442_1445_0.pdf'";

$sql = "select * from publications where issn='1560-2745' and doi is null";

$sql = "select * from publications where guid IN (
'https://www.fungaldiversity.org/fdp/sfdp/27_15.pdf',
'https://www.fungaldiversity.org/fdp/sfdp/27_11.pdf',
'https://www.fungaldiversity.org/fdp/sfdp/27_9.pdf',
'https://www.fungaldiversity.org/fdp/sfdp/27_8.pdf'


)";

//$sql = "select * from publications where guid ='https://www.fungaldiversity.org/fdp/sfdp/22-3.pdf'";

//$sql = "select * from `occasional papers texas tech university museum` where volume > 368";


//$sql = 'SELECT * FROM `publications-revu-suisse-zoologie` ';

//$sql = 'SELECT * FROM publications WHERE guid in ("9a8331a75e0043eaf4ca63cef544c27c", "49a5a81488a1fbda7122797aa502533f", "7a2d0f3996ff19a9acab07cde78449a7", "68d23a59d1df63fca39e7c47bba830b4","ddf0f886d194d04e44f86b8448baa232","8294ef0066e996a41543c96214db2bb9","da445994a97b6caf79ed7377fe7fd066","c359f3f8e1d055fb3558c01590703da3")';

//$sql = 'SELECT * FROM publications WHERE guid="http://rcin.org.pl/dlibra/doccontent?id=57921"';


// $sql = 'SELECT * FROM publications_amnh WHERE pdf IS NOT NULL';
//$sql .= ' ORDER BY CAST(volume as SIGNED), issue, CAST(spage as SIGNED)';
$sql .= ' ORDER BY CAST(volume as SIGNED), issue, CAST(article_number as SIGNED), CAST(spage as SIGNED)';

//$sql .= ' LIMIT 1000 OFFSET 1000';

// citation parsing
//$sql = 'SELECT * FROM publications WHERE title LIKE "%Mr.%" LIMIT 10;';
//$sql = 'SELECT * FROM publications WHERE title LIKE "%n. sp.%" LIMIT 10;';
//$sql = 'SELECT * FROM publications WHERE title REGEXP "[0-9]{4} " AND year=1980 LIMIT 10;';



$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$ris = '';
	
	$ris .= "TY  - JOUR\n";
	
	if ( $result->fields['oai'] != '')
	{
		$ris .= "ID  - " . $result->fields['oai'] . "\n";	
	}
	else
	{ 
		$ris .= "ID  - " . $result->fields['guid'] . "\n";
	}


	foreach ($result->fields as $k => $v)
	{
		switch ($k)
		{
			case 'authors':
			//echo $v . "\n";
				if ($v != '')
				{
					$authors = preg_split("/;/u", $v);
					foreach ($authors as $a)
					{
						if (trim($a) != "")
						{
							$ris .= "AU  - " . $a ."\n";
						}
						//$ris .= "AU  - " . $a ."\n";
					}
					//$ris .= $authors[0] . "\n";
				}
				break;
				
			case 'date':
				if (strlen($v) == 4)
				{
					$ris .= "PY" . "  - " . str_replace('-', '/', $result->fields['date']) . "///\n";
				}
				break;
				
				
			case 'year':
					if ($result->fields['date'] == '')
					{
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
					else
					{
						$ris .= "PY" . "  - " . str_replace('-', '/', $result->fields['date']) . "/\n";
					}
				break;
				
			case 'sha1':
				// http://bionames.org/bionames-archive/pdf/ad/b5/d6/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1/adb5d616e36dd2489bc2fc4eff2f3c878f29d8e1.pdf
				$pdf = 'http://bionames.org/bionames-archive/pdfstore?sha1=' . $v;				
				$ris .= "L1  - " . $pdf . "\n";				
				break;
				
			case 'pdf':
				$go = true;
				//$go = false;
				// check if local file stored in PII
				if ($result->fields['pii'] != '')
				{
					//$ris .= $field_to_ris_key[$k] . "  - " . $result->fields['pii'] . "\n";
					$ris .= $field_to_ris_key[$k] . "  - file://" . $result->fields['pii'] . ".pdf" . "\n";
					
					$go = false;
					
				}
				
				// check if stored in XML
				if ($result->fields['xml'] != '' && preg_match('/\.pdf$/i', $result->fields['xml']))
				{
					$ris .= $field_to_ris_key[$k] . "  - " . $result->fields['xml'] . "\n";
					//$ris .= $field_to_ris_key[$k] . "  - file://" . $result->fields['pii'] . ".pdf" . "\n";
					
					$go = false;
					
				}
				
				
				if ($go)
				{
					if ($result->fields['sha1'] == '')
					{
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
						
						
					}
				}
				break;
				
			case 'spage':
			case 'epage':
				if ($no_pages)
				{
					// eat as data is bad
				}
				else
				{
					if ($v != '')
					{
						$ris .= $field_to_ris_key[$k] . "  - " .  $v . "\n"; 					
					}
				}
				break;
				
			case 'url':
				$go = true;
				$go = false;
				if ($go)
				{
					$ris .= $field_to_ris_key[$k] . "  - " .  $v . "\n"; 	
				}
				break;
				
				
			default:
				if ($v != '')
				{
					if (isset($field_to_ris_key[$k]))
					{
						// clean
						//$v = preg_replace('/\s＂\s/u', '"', $v);
						
						if ($k == 'journal')
						{
							if ($result->fields['series'] != '')
							{
								$v .= ' series ' . $result->fields['series'];
							}
						}
						
					
						$ris .= $field_to_ris_key[$k] . "  - " .  html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\n"; 
					}
				}
				break;
		}
	}
	
	$ris .= "ER  - \n";
	echo $ris . "\n";
	
	$result->MoveNext();
}
	


?>