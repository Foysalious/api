<?php

use App\Models\HyperLocal;

Route::get('/', function () {
    $areas = "[{\"name\":\"dhanmondi\",\"coords\":[{\"lat\":23.75107,\"lng\":90.37828},{\"lat\":23.75276,\"lng\":90.38176},{\"lat\":23.75219,\"lng\":90.38521},{\"lat\":23.75096,\"lng\":90.38694},{\"lat\":23.74995,\"lng\":90.39313},{\"lat\":23.73939,\"lng\":90.39107},{\"lat\":23.73766,\"lng\":90.39034},{\"lat\":23.7356,\"lng\":90.38875},{\"lat\":23.73277,\"lng\":90.38631},{\"lat\":23.73276,\"lng\":90.38337},{\"lat\":23.73253,\"lng\":90.38164},{\"lat\":23.73084,\"lng\":90.37855},{\"lat\":23.72922,\"lng\":90.37614},{\"lat\":23.73129,\"lng\":90.37397},{\"lat\":23.73256,\"lng\":90.37065},{\"lat\":23.73799,\"lng\":90.37008},{\"lat\":23.73969,\"lng\":90.37548},{\"lat\":23.74137,\"lng\":90.37484},{\"lat\":23.74103,\"lng\":90.37066},{\"lat\":23.74164,\"lng\":90.36614},{\"lat\":23.74406,\"lng\":90.36658},{\"lat\":23.74561,\"lng\":90.37056},{\"lat\":23.74525,\"lng\":90.37217},{\"lat\":23.75176,\"lng\":90.36766},{\"lat\":23.75648,\"lng\":90.37529}],\"density\":0.5,\"id\":3},{\"name\":\"mohammadpur\",\"coords\":[{\"lat\":23.77722,\"lng\":90.36343},{\"lat\":23.77345,\"lng\":90.36841},{\"lat\":23.76519,\"lng\":90.37114},{\"lat\":23.75607,\"lng\":90.37606},{\"lat\":23.75174,\"lng\":90.36828},{\"lat\":23.75434,\"lng\":90.36484},{\"lat\":23.75305,\"lng\":90.36449},{\"lat\":23.74991,\"lng\":90.36205},{\"lat\":23.75022,\"lng\":90.35988},{\"lat\":23.74896,\"lng\":90.35837},{\"lat\":23.74746,\"lng\":90.35675},{\"lat\":23.74483,\"lng\":90.358},{\"lat\":23.7432,\"lng\":90.35803},{\"lat\":23.74176,\"lng\":90.35787},{\"lat\":23.74111,\"lng\":90.35487},{\"lat\":23.73969,\"lng\":90.35303},{\"lat\":23.74126,\"lng\":90.34953},{\"lat\":23.74778,\"lng\":90.34212},{\"lat\":23.74978,\"lng\":90.33859},{\"lat\":23.74866,\"lng\":90.33484},{\"lat\":23.74952,\"lng\":90.33152},{\"lat\":23.75026,\"lng\":90.33002},{\"lat\":23.75188,\"lng\":90.33099},{\"lat\":23.75325,\"lng\":90.33328},{\"lat\":23.75503,\"lng\":90.33339},{\"lat\":23.75851,\"lng\":90.33688},{\"lat\":23.76372,\"lng\":90.33969},{\"lat\":23.7656,\"lng\":90.34102},{\"lat\":23.76788,\"lng\":90.34417},{\"lat\":23.76988,\"lng\":90.34515},{\"lat\":23.77143,\"lng\":90.34478},{\"lat\":23.77432,\"lng\":90.34828},{\"lat\":23.77852,\"lng\":90.34943},{\"lat\":23.77816,\"lng\":90.35155},{\"lat\":23.77695,\"lng\":90.35131},{\"lat\":23.77462,\"lng\":90.35051},{\"lat\":23.77544,\"lng\":90.35211},{\"lat\":23.77659,\"lng\":90.35393},{\"lat\":23.77732,\"lng\":90.35985},{\"lat\":23.77846,\"lng\":90.36139}],\"density\":0.35,\"id\":1},{\"name\":\"matijheel\",\"coords\":[{\"lat\":23.74745,\"lng\":90.42343},{\"lat\":23.74539,\"lng\":90.42517},{\"lat\":23.74348,\"lng\":90.42605},{\"lat\":23.72983,\"lng\":90.42658},{\"lat\":23.72729,\"lng\":90.42797},{\"lat\":23.72239,\"lng\":90.42781},{\"lat\":23.72142,\"lng\":90.4228},{\"lat\":23.72217,\"lng\":90.41675},{\"lat\":23.72322,\"lng\":90.41016},{\"lat\":23.72794,\"lng\":90.41003},{\"lat\":23.73022,\"lng\":90.41021},{\"lat\":23.73655,\"lng\":90.41065},{\"lat\":23.73744,\"lng\":90.40881},{\"lat\":23.74346,\"lng\":90.41373},{\"lat\":23.74537,\"lng\":90.41207},{\"lat\":23.74916,\"lng\":90.41263}],\"density\":0.5,\"id\":6},{\"name\":\"kafrul\",\"coords\":[{\"lat\":23.7744,\"lng\":90.39081},{\"lat\":23.77571,\"lng\":90.39281},{\"lat\":23.77624,\"lng\":90.39506},{\"lat\":23.77856,\"lng\":90.39862},{\"lat\":23.80103,\"lng\":90.40236},{\"lat\":23.80284,\"lng\":90.40379},{\"lat\":23.80469,\"lng\":90.40623},{\"lat\":23.80632,\"lng\":90.40676},{\"lat\":23.80768,\"lng\":90.40531},{\"lat\":23.81441,\"lng\":90.40725},{\"lat\":23.81585,\"lng\":90.40534},{\"lat\":23.81698,\"lng\":90.40137},{\"lat\":23.81704,\"lng\":90.40061},{\"lat\":23.81584,\"lng\":90.40019},{\"lat\":23.81534,\"lng\":90.40055},{\"lat\":23.81024,\"lng\":90.39922},{\"lat\":23.80571,\"lng\":90.3987},{\"lat\":23.80405,\"lng\":90.39733},{\"lat\":23.80181,\"lng\":90.39525},{\"lat\":23.79954,\"lng\":90.39351},{\"lat\":23.80002,\"lng\":90.38935},{\"lat\":23.80584,\"lng\":90.38823},{\"lat\":23.8109,\"lng\":90.38907},{\"lat\":23.81127,\"lng\":90.3845},{\"lat\":23.81081,\"lng\":90.38267},{\"lat\":23.81097,\"lng\":90.37947},{\"lat\":23.80949,\"lng\":90.37727},{\"lat\":23.80812,\"lng\":90.37609},{\"lat\":23.80627,\"lng\":90.3761},{\"lat\":23.80773,\"lng\":90.37226},{\"lat\":23.80705,\"lng\":90.36828},{\"lat\":23.80427,\"lng\":90.36929},{\"lat\":23.80365,\"lng\":90.37114},{\"lat\":23.80178,\"lng\":90.37121},{\"lat\":23.80021,\"lng\":90.37231},{\"lat\":23.79882,\"lng\":90.37052},{\"lat\":23.79809,\"lng\":90.37089},{\"lat\":23.79725,\"lng\":90.36993},{\"lat\":23.794,\"lng\":90.37125},{\"lat\":23.79363,\"lng\":90.37218},{\"lat\":23.79257,\"lng\":90.37216},{\"lat\":23.7903,\"lng\":90.37298},{\"lat\":23.79015,\"lng\":90.37495},{\"lat\":23.78293,\"lng\":90.37563},{\"lat\":23.7835,\"lng\":90.37906},{\"lat\":23.78115,\"lng\":90.38022},{\"lat\":23.77094,\"lng\":90.3832},{\"lat\":23.76265,\"lng\":90.38439},{\"lat\":23.76625,\"lng\":90.38512},{\"lat\":23.76654,\"lng\":90.38748},{\"lat\":23.7696,\"lng\":90.38778},{\"lat\":23.77078,\"lng\":90.39005}],\"density\":0.9,\"id\":26},{\"name\":\"gulshan\",\"coords\":[{\"lat\":23.81535,\"lng\":90.41356},{\"lat\":23.81272,\"lng\":90.41352},{\"lat\":23.80993,\"lng\":90.41364},{\"lat\":23.80933,\"lng\":90.41585},{\"lat\":23.81036,\"lng\":90.4209},{\"lat\":23.80994,\"lng\":90.42325},{\"lat\":23.80933,\"lng\":90.42666},{\"lat\":23.80729,\"lng\":90.42704},{\"lat\":23.80526,\"lng\":90.42656},{\"lat\":23.80417,\"lng\":90.42303},{\"lat\":23.79196,\"lng\":90.42538},{\"lat\":23.7875,\"lng\":90.42647},{\"lat\":23.78743,\"lng\":90.42803},{\"lat\":23.7869,\"lng\":90.42993},{\"lat\":23.78569,\"lng\":90.43043},{\"lat\":23.78482,\"lng\":90.42476},{\"lat\":23.78521,\"lng\":90.4239},{\"lat\":23.78434,\"lng\":90.4196},{\"lat\":23.78472,\"lng\":90.41788},{\"lat\":23.77888,\"lng\":90.41769},{\"lat\":23.77234,\"lng\":90.41624},{\"lat\":23.77154,\"lng\":90.41313},{\"lat\":23.77232,\"lng\":90.40968},{\"lat\":23.7723,\"lng\":90.40759},{\"lat\":23.77384,\"lng\":90.40307},{\"lat\":23.77378,\"lng\":90.40054},{\"lat\":23.77474,\"lng\":90.39908},{\"lat\":23.77856,\"lng\":90.39841},{\"lat\":23.80096,\"lng\":90.40226},{\"lat\":23.80296,\"lng\":90.40376},{\"lat\":23.80389,\"lng\":90.40545},{\"lat\":23.80451,\"lng\":90.40612},{\"lat\":23.8067,\"lng\":90.40663},{\"lat\":23.80835,\"lng\":90.40543},{\"lat\":23.81226,\"lng\":90.40603},{\"lat\":23.81506,\"lng\":90.40749},{\"lat\":23.81541,\"lng\":90.41049}],\"density\":0.7,\"id\":4},{\"name\":\"khilgaon\",\"coords\":[{\"lat\":23.76988,\"lng\":90.4824},{\"lat\":23.76737,\"lng\":90.48153},{\"lat\":23.76202,\"lng\":90.48255},{\"lat\":23.75951,\"lng\":90.48255},{\"lat\":23.75778,\"lng\":90.48315},{\"lat\":23.75645,\"lng\":90.48577},{\"lat\":23.75552,\"lng\":90.48789},{\"lat\":23.75364,\"lng\":90.48796},{\"lat\":23.751,\"lng\":90.48551},{\"lat\":23.74948,\"lng\":90.48509},{\"lat\":23.74643,\"lng\":90.48682},{\"lat\":23.74323,\"lng\":90.48468},{\"lat\":23.74238,\"lng\":90.48272},{\"lat\":23.74317,\"lng\":90.47987},{\"lat\":23.74238,\"lng\":90.47682},{\"lat\":23.74191,\"lng\":90.47521},{\"lat\":23.74317,\"lng\":90.47191},{\"lat\":23.74482,\"lng\":90.47018},{\"lat\":23.74792,\"lng\":90.46828},{\"lat\":23.74757,\"lng\":90.46656},{\"lat\":23.74494,\"lng\":90.46636},{\"lat\":23.74325,\"lng\":90.46531},{\"lat\":23.74239,\"lng\":90.46354},{\"lat\":23.73879,\"lng\":90.46241},{\"lat\":23.73708,\"lng\":90.46058},{\"lat\":23.73649,\"lng\":90.45821},{\"lat\":23.73694,\"lng\":90.45487},{\"lat\":23.73848,\"lng\":90.45341},{\"lat\":23.74032,\"lng\":90.45049},{\"lat\":23.74162,\"lng\":90.44672},{\"lat\":23.74423,\"lng\":90.44296},{\"lat\":23.74198,\"lng\":90.43895},{\"lat\":23.74053,\"lng\":90.43479},{\"lat\":23.7424,\"lng\":90.43168},{\"lat\":23.74239,\"lng\":90.42617},{\"lat\":23.74544,\"lng\":90.42485},{\"lat\":23.74747,\"lng\":90.42346},{\"lat\":23.74876,\"lng\":90.41817},{\"lat\":23.74933,\"lng\":90.41235},{\"lat\":23.75653,\"lng\":90.4164},{\"lat\":23.75934,\"lng\":90.41816},{\"lat\":23.76185,\"lng\":90.4175},{\"lat\":23.76208,\"lng\":90.41511},{\"lat\":23.76266,\"lng\":90.414},{\"lat\":23.76406,\"lng\":90.41275},{\"lat\":23.76546,\"lng\":90.41205},{\"lat\":23.76773,\"lng\":90.41431},{\"lat\":23.76859,\"lng\":90.41726},{\"lat\":23.77044,\"lng\":90.41573},{\"lat\":23.77194,\"lng\":90.41508},{\"lat\":23.77221,\"lng\":90.41612},{\"lat\":23.77164,\"lng\":90.41732},{\"lat\":23.77234,\"lng\":90.41788},{\"lat\":23.77108,\"lng\":90.41863},{\"lat\":23.77068,\"lng\":90.41995},{\"lat\":23.77025,\"lng\":90.42138},{\"lat\":23.76898,\"lng\":90.42286},{\"lat\":23.76771,\"lng\":90.4233},{\"lat\":23.76491,\"lng\":90.42838},{\"lat\":23.76306,\"lng\":90.43363},{\"lat\":23.76388,\"lng\":90.4369},{\"lat\":23.76594,\"lng\":90.43793},{\"lat\":23.76665,\"lng\":90.43956},{\"lat\":23.76685,\"lng\":90.44158},{\"lat\":23.76813,\"lng\":90.44208},{\"lat\":23.76956,\"lng\":90.44413},{\"lat\":23.76925,\"lng\":90.44532},{\"lat\":23.76776,\"lng\":90.44437},{\"lat\":23.76521,\"lng\":90.44347},{\"lat\":23.76511,\"lng\":90.44491},{\"lat\":23.76388,\"lng\":90.44511},{\"lat\":23.76303,\"lng\":90.44341},{\"lat\":23.76214,\"lng\":90.44299},{\"lat\":23.76161,\"lng\":90.44527},{\"lat\":23.76064,\"lng\":90.44641},{\"lat\":23.7611,\"lng\":90.44732},{\"lat\":23.7618,\"lng\":90.44855},{\"lat\":23.76356,\"lng\":90.44865},{\"lat\":23.76405,\"lng\":90.44741},{\"lat\":23.76539,\"lng\":90.44722},{\"lat\":23.7674,\"lng\":90.4497},{\"lat\":23.76966,\"lng\":90.45008},{\"lat\":23.77087,\"lng\":90.45156},{\"lat\":23.77195,\"lng\":90.45299},{\"lat\":23.77288,\"lng\":90.45619},{\"lat\":23.77291,\"lng\":90.45783},{\"lat\":23.77356,\"lng\":90.45883},{\"lat\":23.77521,\"lng\":90.45941},{\"lat\":23.77641,\"lng\":90.46037},{\"lat\":23.77662,\"lng\":90.46317},{\"lat\":23.77798,\"lng\":90.46457},{\"lat\":23.77887,\"lng\":90.467},{\"lat\":23.778,\"lng\":90.47115},{\"lat\":23.77869,\"lng\":90.47375},{\"lat\":23.77783,\"lng\":90.47488},{\"lat\":23.7779,\"lng\":90.47687},{\"lat\":23.77436,\"lng\":90.48101},{\"lat\":23.77141,\"lng\":90.48247}],\"density\":0.95,\"id\":29},{\"name\":\"lalbag\",\"coords\":[{\"lat\":23.72922,\"lng\":90.38725},{\"lat\":23.7262,\"lng\":90.39026},{\"lat\":23.72414,\"lng\":90.39322},{\"lat\":23.72334,\"lng\":90.39908},{\"lat\":23.72098,\"lng\":90.39728},{\"lat\":23.71885,\"lng\":90.39733},{\"lat\":23.71913,\"lng\":90.39899},{\"lat\":23.7173,\"lng\":90.40127},{\"lat\":23.7123,\"lng\":90.40164},{\"lat\":23.7106,\"lng\":90.40116},{\"lat\":23.71086,\"lng\":90.39146},{\"lat\":23.712,\"lng\":90.3903},{\"lat\":23.71197,\"lng\":90.38751},{\"lat\":23.71345,\"lng\":90.38444},{\"lat\":23.71541,\"lng\":90.37982},{\"lat\":23.71728,\"lng\":90.37875},{\"lat\":23.72039,\"lng\":90.37849},{\"lat\":23.72152,\"lng\":90.37578},{\"lat\":23.72328,\"lng\":90.37426},{\"lat\":23.72655,\"lng\":90.37716},{\"lat\":23.72887,\"lng\":90.37642},{\"lat\":23.73048,\"lng\":90.3786},{\"lat\":23.73209,\"lng\":90.38215},{\"lat\":23.73215,\"lng\":90.38676}],\"density\":0.8,\"id\":30},{\"name\":\"mirpur\",\"coords\":[{\"lat\":23.82803,\"lng\":90.3461},{\"lat\":23.83031,\"lng\":90.3502},{\"lat\":23.82882,\"lng\":90.3543},{\"lat\":23.82616,\"lng\":90.35391},{\"lat\":23.82271,\"lng\":90.35212},{\"lat\":23.81989,\"lng\":90.35299},{\"lat\":23.81897,\"lng\":90.35543},{\"lat\":23.81588,\"lng\":90.35652},{\"lat\":23.81535,\"lng\":90.35425},{\"lat\":23.80801,\"lng\":90.35519},{\"lat\":23.80683,\"lng\":90.35227},{\"lat\":23.80069,\"lng\":90.35604},{\"lat\":23.80414,\"lng\":90.3605},{\"lat\":23.80725,\"lng\":90.36872},{\"lat\":23.80406,\"lng\":90.37006},{\"lat\":23.80395,\"lng\":90.37101},{\"lat\":23.80249,\"lng\":90.37155},{\"lat\":23.80082,\"lng\":90.37229},{\"lat\":23.79998,\"lng\":90.37135},{\"lat\":23.79737,\"lng\":90.37018},{\"lat\":23.79435,\"lng\":90.37091},{\"lat\":23.79302,\"lng\":90.37307},{\"lat\":23.79067,\"lng\":90.37326},{\"lat\":23.78975,\"lng\":90.37502},{\"lat\":23.78288,\"lng\":90.37579},{\"lat\":23.78237,\"lng\":90.36864},{\"lat\":23.78249,\"lng\":90.36457},{\"lat\":23.77739,\"lng\":90.35987},{\"lat\":23.77629,\"lng\":90.35252},{\"lat\":23.77505,\"lng\":90.35088},{\"lat\":23.77821,\"lng\":90.35103},{\"lat\":23.77857,\"lng\":90.34892},{\"lat\":23.77426,\"lng\":90.34779},{\"lat\":23.7713,\"lng\":90.34485},{\"lat\":23.77352,\"lng\":90.34148},{\"lat\":23.77473,\"lng\":90.33578},{\"lat\":23.77613,\"lng\":90.33533},{\"lat\":23.77902,\"lng\":90.33802},{\"lat\":23.78157,\"lng\":90.3368},{\"lat\":23.78395,\"lng\":90.33635},{\"lat\":23.78827,\"lng\":90.33974},{\"lat\":23.79421,\"lng\":90.34074},{\"lat\":23.79717,\"lng\":90.34039},{\"lat\":23.79897,\"lng\":90.34399},{\"lat\":23.80223,\"lng\":90.34493},{\"lat\":23.80652,\"lng\":90.34248},{\"lat\":23.80977,\"lng\":90.34074},{\"lat\":23.81359,\"lng\":90.34004},{\"lat\":23.8193,\"lng\":90.34106},{\"lat\":23.8197,\"lng\":90.3403},{\"lat\":23.82143,\"lng\":90.33843},{\"lat\":23.82457,\"lng\":90.34053}],\"density\":0.49,\"id\":5},{\"name\":\"badda\",\"coords\":[{\"lat\":23.8446,\"lng\":90.45699},{\"lat\":23.84515,\"lng\":90.46042},{\"lat\":23.84664,\"lng\":90.46118},{\"lat\":23.84758,\"lng\":90.46391},{\"lat\":23.84665,\"lng\":90.46646},{\"lat\":23.84649,\"lng\":90.47086},{\"lat\":23.84493,\"lng\":90.47143},{\"lat\":23.84495,\"lng\":90.47771},{\"lat\":23.84468,\"lng\":90.47877},{\"lat\":23.84429,\"lng\":90.47952},{\"lat\":23.84116,\"lng\":90.48102},{\"lat\":23.83787,\"lng\":90.48454},{\"lat\":23.83382,\"lng\":90.48644},{\"lat\":23.83199,\"lng\":90.48953},{\"lat\":23.831,\"lng\":90.49349},{\"lat\":23.8284,\"lng\":90.49386},{\"lat\":23.82091,\"lng\":90.49426},{\"lat\":23.81519,\"lng\":90.49334},{\"lat\":23.80785,\"lng\":90.49218},{\"lat\":23.80416,\"lng\":90.49089},{\"lat\":23.79927,\"lng\":90.48855},{\"lat\":23.79352,\"lng\":90.48664},{\"lat\":23.78893,\"lng\":90.48333},{\"lat\":23.78571,\"lng\":90.47843},{\"lat\":23.78337,\"lng\":90.47447},{\"lat\":23.78166,\"lng\":90.47445},{\"lat\":23.77793,\"lng\":90.47681},{\"lat\":23.77785,\"lng\":90.47449},{\"lat\":23.77878,\"lng\":90.47345},{\"lat\":23.77815,\"lng\":90.47102},{\"lat\":23.77891,\"lng\":90.46703},{\"lat\":23.77792,\"lng\":90.46455},{\"lat\":23.77689,\"lng\":90.46318},{\"lat\":23.77641,\"lng\":90.46027},{\"lat\":23.77377,\"lng\":90.45894},{\"lat\":23.77285,\"lng\":90.45762},{\"lat\":23.77292,\"lng\":90.45599},{\"lat\":23.77206,\"lng\":90.45311},{\"lat\":23.76958,\"lng\":90.45009},{\"lat\":23.76727,\"lng\":90.44954},{\"lat\":23.76548,\"lng\":90.4469},{\"lat\":23.76411,\"lng\":90.44746},{\"lat\":23.7634,\"lng\":90.44858},{\"lat\":23.76183,\"lng\":90.44858},{\"lat\":23.76062,\"lng\":90.44647},{\"lat\":23.76161,\"lng\":90.44546},{\"lat\":23.76245,\"lng\":90.4429},{\"lat\":23.76302,\"lng\":90.44379},{\"lat\":23.76417,\"lng\":90.44488},{\"lat\":23.76536,\"lng\":90.44483},{\"lat\":23.76539,\"lng\":90.44371},{\"lat\":23.76741,\"lng\":90.44447},{\"lat\":23.7691,\"lng\":90.44521},{\"lat\":23.76973,\"lng\":90.44396},{\"lat\":23.76832,\"lng\":90.44179},{\"lat\":23.76707,\"lng\":90.44105},{\"lat\":23.76677,\"lng\":90.43924},{\"lat\":23.76609,\"lng\":90.43809},{\"lat\":23.7641,\"lng\":90.43684},{\"lat\":23.76318,\"lng\":90.43373},{\"lat\":23.76473,\"lng\":90.42869},{\"lat\":23.76769,\"lng\":90.4233},{\"lat\":23.76921,\"lng\":90.42265},{\"lat\":23.77036,\"lng\":90.42102},{\"lat\":23.77078,\"lng\":90.41877},{\"lat\":23.77293,\"lng\":90.4175},{\"lat\":23.77253,\"lng\":90.41634},{\"lat\":23.7787,\"lng\":90.41736},{\"lat\":23.78464,\"lng\":90.41777},{\"lat\":23.78431,\"lng\":90.41962},{\"lat\":23.78532,\"lng\":90.4238},{\"lat\":23.78465,\"lng\":90.42469},{\"lat\":23.78557,\"lng\":90.43011},{\"lat\":23.7869,\"lng\":90.4299},{\"lat\":23.78748,\"lng\":90.428},{\"lat\":23.78761,\"lng\":90.42636},{\"lat\":23.80402,\"lng\":90.42279},{\"lat\":23.80522,\"lng\":90.42658},{\"lat\":23.80708,\"lng\":90.42693},{\"lat\":23.80925,\"lng\":90.4266},{\"lat\":23.81056,\"lng\":90.42044},{\"lat\":23.80925,\"lng\":90.4157},{\"lat\":23.81009,\"lng\":90.41341},{\"lat\":23.81552,\"lng\":90.41347},{\"lat\":23.82185,\"lng\":90.41496},{\"lat\":23.82366,\"lng\":90.41641},{\"lat\":23.82824,\"lng\":90.41946},{\"lat\":23.83,\"lng\":90.42128},{\"lat\":23.83207,\"lng\":90.42293},{\"lat\":23.83481,\"lng\":90.42896},{\"lat\":23.83683,\"lng\":90.43057},{\"lat\":23.8371,\"lng\":90.43327},{\"lat\":23.83497,\"lng\":90.43437},{\"lat\":23.83417,\"lng\":90.4388},{\"lat\":23.83524,\"lng\":90.44475},{\"lat\":23.8355,\"lng\":90.44892},{\"lat\":23.84015,\"lng\":90.45379},{\"lat\":23.84316,\"lng\":90.45402}],\"density\":0.3,\"id\":20},{\"name\":\"uttara\",\"coords\":[{\"lat\":23.88753,\"lng\":90.46523},{\"lat\":23.88454,\"lng\":90.46789},{\"lat\":23.87755,\"lng\":90.47051},{\"lat\":23.87377,\"lng\":90.47273},{\"lat\":23.87197,\"lng\":90.47448},{\"lat\":23.86926,\"lng\":90.4772},{\"lat\":23.86508,\"lng\":90.48023},{\"lat\":23.86192,\"lng\":90.48163},{\"lat\":23.85649,\"lng\":90.48099},{\"lat\":23.84894,\"lng\":90.47941},{\"lat\":23.84511,\"lng\":90.47823},{\"lat\":23.84515,\"lng\":90.47139},{\"lat\":23.84681,\"lng\":90.47078},{\"lat\":23.84682,\"lng\":90.46629},{\"lat\":23.84778,\"lng\":90.46365},{\"lat\":23.84665,\"lng\":90.46088},{\"lat\":23.84501,\"lng\":90.45979},{\"lat\":23.84488,\"lng\":90.45658},{\"lat\":23.84351,\"lng\":90.45393},{\"lat\":23.84014,\"lng\":90.45379},{\"lat\":23.83544,\"lng\":90.44888},{\"lat\":23.83547,\"lng\":90.44488},{\"lat\":23.83411,\"lng\":90.43862},{\"lat\":23.83516,\"lng\":90.43398},{\"lat\":23.83725,\"lng\":90.43311},{\"lat\":23.8369,\"lng\":90.43034},{\"lat\":23.83508,\"lng\":90.42909},{\"lat\":23.83192,\"lng\":90.42249},{\"lat\":23.82835,\"lng\":90.41922},{\"lat\":23.82877,\"lng\":90.41207},{\"lat\":23.82951,\"lng\":90.40869},{\"lat\":23.82941,\"lng\":90.4021},{\"lat\":23.83433,\"lng\":90.402},{\"lat\":23.83712,\"lng\":90.40105},{\"lat\":23.84105,\"lng\":90.40158},{\"lat\":23.84435,\"lng\":90.39927},{\"lat\":23.84616,\"lng\":90.39554},{\"lat\":23.84942,\"lng\":90.39119},{\"lat\":23.84979,\"lng\":90.38802},{\"lat\":23.84951,\"lng\":90.38597},{\"lat\":23.84968,\"lng\":90.3846},{\"lat\":23.85922,\"lng\":90.38044},{\"lat\":23.86144,\"lng\":90.37788},{\"lat\":23.86695,\"lng\":90.37781},{\"lat\":23.86939,\"lng\":90.37629},{\"lat\":23.87143,\"lng\":90.37434},{\"lat\":23.87613,\"lng\":90.37133},{\"lat\":23.87676,\"lng\":90.36544},{\"lat\":23.87652,\"lng\":90.35718},{\"lat\":23.87672,\"lng\":90.35348},{\"lat\":23.87917,\"lng\":90.35128},{\"lat\":23.88338,\"lng\":90.35053},{\"lat\":23.88514,\"lng\":90.35214},{\"lat\":23.88721,\"lng\":90.35582},{\"lat\":23.88771,\"lng\":90.35846},{\"lat\":23.88957,\"lng\":90.35865},{\"lat\":23.89205,\"lng\":90.36055},{\"lat\":23.89311,\"lng\":90.35928},{\"lat\":23.89622,\"lng\":90.36016},{\"lat\":23.89834,\"lng\":90.36672},{\"lat\":23.89919,\"lng\":90.3731},{\"lat\":23.90012,\"lng\":90.37753},{\"lat\":23.90269,\"lng\":90.38256},{\"lat\":23.90131,\"lng\":90.38902},{\"lat\":23.89575,\"lng\":90.39268},{\"lat\":23.89289,\"lng\":90.3952},{\"lat\":23.89051,\"lng\":90.39497},{\"lat\":23.88353,\"lng\":90.39654},{\"lat\":23.88369,\"lng\":90.39935},{\"lat\":23.8845,\"lng\":90.40737},{\"lat\":23.88564,\"lng\":90.41584},{\"lat\":23.89104,\"lng\":90.42516},{\"lat\":23.89554,\"lng\":90.43116},{\"lat\":23.89738,\"lng\":90.43534},{\"lat\":23.90004,\"lng\":90.4388},{\"lat\":23.90309,\"lng\":90.44492},{\"lat\":23.9012,\"lng\":90.45138},{\"lat\":23.89884,\"lng\":90.4559},{\"lat\":23.89691,\"lng\":90.46168},{\"lat\":23.89599,\"lng\":90.46479},{\"lat\":23.89145,\"lng\":90.46561}],\"density\":0.6,\"id\":7}]";
    foreach (json_decode($areas) as $area) {
        $location = \App\Models\Location::find($area->id);
        $coords = collect();
        if ($location) {
            foreach ($area->coords as $coord) {
                $coords->push([$coord->lng, $coord->lat]);
            }
            $coords = $coords->toArray();
            array_push($coords, $coords[0]);
            if ($location->geo_informations) {
                $geo = json_decode($location->geo_informations);
                $geo->geometry = array(
                    'type' => 'Polygon',
                    'coordinates' => [$coords]
                );
                $location->geo_informations = json_encode($geo);
            } else {
                $location->geo_informations = json_encode(array(
                    'geometry' => array(
                        'type' => 'Polygon',
                        'coordinates' => [$coords]
                    )
                ));
            }
            $location->update();
            $lowx = $highx = $lowy = $highy = $center_x = $center_y = 0;
            $lats = [];
            $lngs = [];
            $vertices = count($coords);
            for ($i = 0; $i < $vertices; $i++) {
                array_push($lats, $coords[$i][1]);
                array_push($lngs, $coords[$i][0]);
            }
            sort($lats);
            sort($lngs);
            $lowx = $lats[0];
            $highx = $lats[$vertices - 1];
            $lowy = $lngs[0];
            $highy = $lngs[$vertices - 1];
            $center_x = $lowx + (($highx - $lowx) / 2);
            $center_y = $lowy + (($highy - $lowy) / 2);
            $geo = json_decode($location->geo_informations);
            $geo->center = array(
                'lat' => $center_x,
                'lng' => $center_y
            );
            $location->geo_informations = json_encode($geo);
            $location->update();
            if ($location->hyperLocal == null) {
                $local = new HyperLocal();
                $local->location = json_decode($location->geo_informations)->geometry;
                $local->location_id = $location->id;
                $local->save();
            } else {
                $local = $location->hyperLocal;
                $local->location = json_decode($location->geo_informations)->geometry;
                $local->save();
            }
        }
    }
    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
});
$api = app('Dingo\Api\Routing\Router');

/*
|--------------------------------------------------------------------------
| Version Reminder
|--------------------------------------------------------------------------
|
| When next version comes add a prefix to the old version
| routes and change API_PREFIX in api.php file to null
|
|
*/
$api->version('v1', function ($api) {
    $api->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers'], function ($api) {
        $api->post('login', 'Auth\LoginController@login');
        $api->post('register', 'Auth\RegistrationController@register');
        $api->group(['prefix' => 'login'], function ($api) {
            $api->post('facebook', 'FacebookController@login');
        });
        $api->group(['prefix' => 'register'], function ($api) {
            $api->post('email', 'Auth\RegistrationController@registerByEmailAndMobile');
            $api->post('facebook', 'FacebookController@register');
        });
        $api->post('continue-with-kit', 'FacebookController@continueWithKit');
        $api->post('continue-with-facebook', 'FacebookController@continueWithFacebook');
        $api->get('authenticate', 'AccountController@checkForAuthentication');
        $api->post('account', 'AccountController@encryptData');
        $api->get('decrypt', 'AccountController@decryptData');
        $api->get('info', 'ShebaController@getInfo');
        $api->get('versions', 'ShebaController@getVersions');
        $api->get('images', 'ShebaController@getImages');
        $api->get('sliders', 'SliderController@index');
        $api->get('locations', 'LocationController@getAllLocations');
        $api->get('lead-reward', 'ShebaController@getLeadRewardAmount');
        $api->get('search', 'SearchController@searchService');
        $api->get('career', 'CareerController@getVacantPosts');
        $api->post('career', 'CareerController@apply');
        $api->get('category-service', 'CategoryServiceController@getCategoryServices');
        $api->get('job-times', 'JobController@getPreferredTimes');
        $api->get('times', 'JobController@getPreferredTimes');
        $api->get('cancel-job-reasons', 'JobController@cancelJobReasons');

        $api->post('voucher-valid', 'CheckoutController@validateVoucher');
        $api->post('vouchers', 'CheckoutController@validateVoucher');

        $api->post('rating', 'ReviewController@giveRatingFromEmail');
        $api->post('sms', 'SmsController@send');
        $api->post('faq', 'ShebaController@sendFaq');
        $api->group(['prefix' => 'offers'], function ($api) {
            $api->get('/', 'OfferController@index');
            $api->get('{offer}', 'OfferController@show');
        });
        $api->get('offer/{offer}/similar', 'ShebaController@getSimilarOffer');

        $api->group(['prefix' => 'navigation'], function ($api) {
            $api->get('/', 'NavigationController@getNavList');
        });
        $api->group(['prefix' => 'jobs'], function ($api) {
            $api->get('times', 'JobController@getPreferredTimes');
        });
        $api->group(['prefix' => 'categories'], function ($api) {
            $api->get('/', 'CategoryController@index');
            $api->get('{category}/secondaries', 'CategoryController@getSecondaries');
            $api->get('{category}/secondaries/services', 'CategoryController@getSecondaryServices');
            $api->get('{category}/services', 'CategoryController@getServices');
            $api->get('{category}/master', 'CategoryController@getMaster');
        });
        $api->group(['prefix' => 'service'], function ($api) {
            $api->get('{service}/get-prices', 'ServiceController@getPrices');
            $api->get('{service}/location/{location}/partners', 'ServiceController@getPartners');
            $api->post('{service}/location/{location}/partners', 'ServiceController@getPartners');
            $api->post('{service}/{location}/change-partner', 'ServiceController@changePartner');
            $api->get('/{service}/reviews', 'ServiceController@getReviews');
            //For Back-end
            $api->post('{service}/change-partner', 'ServiceController@changePartnerWithoutLocation');
        });
        $api->group(['prefix' => 'services'], function ($api) {
            $api->get('/', 'ServiceController@index');
            $api->get('{service}', 'ServiceController@get');
            $api->get('{service}/valid', 'ServiceController@checkForValidity');
            $api->get('{service}/similar', 'ServiceController@getSimilarServices');
            $api->get('{service}/reviews', 'ServiceController@getReviews');
            $api->get('{service}/locations/{location}/partners', 'ServiceController@getPartnersOfLocation');
            $api->post('{service}/locations/{location}/partners', 'ServiceController@getPartners');
        });
        $api->group(['prefix' => 'partner'], function ($api) {
            $api->get('/', 'PartnerController@index');
            $api->get('{partner}/services', 'PartnerController@getPartnerServices');
            $api->get('{partner}/reviews', 'PartnerController@getReviews');
        });
        $api->group(['prefix' => 'customer', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('{customer}', 'CustomerController@getCustomerInfo');
            $api->post('{customer}/edit', 'CustomerController@editInfo');
            $api->get('{customer}/general-info', 'CustomerController@getCustomerGeneralInfo');
            $api->get('{customer}/intercom-info', 'CustomerController@getIntercomInfo');
            $api->get('{customer}/checkout-info', 'CustomerController@getDeliveryInfo');
            $api->get('{customer}/order-list', 'OrderController@getNotClosedOrderInfo');
            $api->get('{customer}/order-history', 'OrderController@getClosedOrderInfo');
            $api->get('{customer}/cancel-order-list', 'OrderController@getCancelledOrders');
            $api->get('{customer}/referral', 'CustomerController@getReferral');
            $api->post('{customer}/send-referral-request-email', 'CustomerController@sendReferralRequestEmail');
            $api->get('{customer}/promo', 'PromotionController@getPromo');
            $api->post('{customer}/promo', 'PromotionController@addPromo');
            $api->post('{customer}/suggest-promo', 'PromotionController@suggestPromo');

            $api->post('{customer}/sp-payment', 'CheckoutController@spPayment');
            $api->post('{customer}/order-valid', 'OrderController@checkOrderValidity');
            $api->post('{customer}/modify-review', 'ReviewController@modifyReview');
            $api->get('{customer}/job/{job}', 'JobController@getInfo');
            $api->post('{customer}/{job}/cancel', 'JobController@cancelJob');

            $api->post('{customer}/ask-quotation', 'CustomOrderController@askForQuotation');
            $api->get('{customer}/custom-order', 'CustomOrderController@getCustomOrders');
            $api->get('{customer}/custom-order/{custom_order}/quotation', 'CustomOrderController@getCustomOrderQuotation');
            $api->get('{customer}/custom-order/{custom_order}/discussion', 'CustomOrderController@getCommentForDiscussion');
            $api->post('{customer}/custom-order/{custom_order}/discussion', 'CustomOrderController@postCommentOnDiscussion');

//            $api->post('{customer}/checkout/place-order', 'CheckoutController@placeOrder');
//            $api->post('{customer}/checkout/place-order-with-online-payment', 'CheckoutController@placeOrderWithPayment');
        });
        $api->group(['prefix' => 'customers/{customer}', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('/', 'CustomerController@index');
            $api->group(['prefix' => 'edit'], function ($api) {
                $api->put('/', 'CustomerController@update');
                $api->put('email', 'CustomerController@updateEmail');
                $api->put('password', 'CustomerController@updatePassword');
                $api->post('picture', 'CustomerController@updatePicture');
                $api->put('mobile', 'CustomerController@updateMobile');
            });
            $api->post('reviews', 'ReviewController@modifyReview');
            $api->get('notifications', 'CustomerController@getNotifications');
            $api->post('suggest-promo', 'PromotionController@suggestPromo');
            $api->put('addresses/{address}', 'CustomerAddressController@update');
        });
        $api->group(['prefix' => 'checkout'], function ($api) {
            $api->get('place-order-final', 'CheckoutController@placeOrderFinal');
            $api->get('sp-payment-final', 'CheckoutController@spPaymentFinal');
        });
        $api->group(['prefix' => 'business'], function ($api) {
            $api->get('check-url', 'BusinessController@checkURL');
            $api->get('type-category', 'BusinessController@getTypeAndCategories');

            $api->group(['prefix' => 'member', 'middleware' => ['member.auth']], function ($api) {
                $api->get('/{member}/get-info', 'MemberController@getInfo');
                $api->get('/{member}/get-profile-info', 'MemberController@getProfileInfo');
                $api->post('/{member}/update-personal-info', 'MemberController@updatePersonalInfo');
                $api->post('/{member}/update-professional-info', 'MemberController@updateProfessionalInfo');
                $api->post('/{member}/change-NID', 'MemberController@changeNID');

                $api->post('/{member}/create-business', 'BusinessController@create');
                $api->post('{member}/check-business', 'BusinessController@checkBusiness');
                $api->get('/{member}/show', 'BusinessController@show');

                $api->get('{member}/business/{business}', 'BusinessController@getBusiness');
                $api->post('{member}/business/{business}/update', 'BusinessController@update');
                $api->post('{member}/business/{business}/change-logo', 'BusinessController@changeLogo');
                $api->get('{member}/business/{business}/members', 'BusinessController@getMembers');
                $api->get('{member}/business/{business}/requests', 'BusinessController@getRequests');
                $api->post('{member}/business/{business}/manage-invitation', 'BusinessController@manageInvitation');
                $api->get('{member}/business/{business}/get-member', 'BusinessMemberController@getMember');
                $api->post('{member}/business/{business}/change-member-type', 'BusinessMemberController@changeMemberType');


                $api->post('{member}/search', 'SearchController@searchBusinessOrMember');
                $api->get('{member}/requests', 'MemberController@getRequests');

                $api->post('{member}/send-invitation', 'InvitationController@sendInvitation');
                $api->post('{member}/manage-invitation', 'MemberController@manageInvitation');
            });
        });
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->get('dashboard', 'PartnerController@getDashboardInfo');
            $api->get('earnings', 'PartnerController@getEarnings');
            $api->get('reviews', 'PartnerController@getReviewInfo');
            $api->get('info', 'PartnerController@getInfo');
            $api->get('notifications', 'PartnerController@getNotifications');

            $api->group(['prefix' => 'withdrawals'], function ($api) {
                $api->get('/', 'PartnerWithdrawalRequestController@index');
                $api->post('/', 'PartnerWithdrawalRequestController@store');
                $api->put('{withdrawals}', 'PartnerWithdrawalRequestController@update');
                $api->get('status', 'PartnerWithdrawalRequestController@getStatus');
            });
            $api->group(['prefix' => 'transactions'], function ($api) {
                $api->get('/', 'PartnerTransactionController@index');
            });

            $api->group(['prefix' => 'graphs'], function ($api) {
                $api->get('orders', 'GraphController@getOrdersGraph');
                $api->get('sales', 'GraphController@getSalesGraph');
            });
            $api->group(['prefix' => 'resources'], function ($api) {
                $api->get('/', 'PartnerController@getResources');

                $api->group(['prefix' => '{resource}', 'middleware' => ['partner_resource.auth']], function ($api) {
                    $api->get('/', 'ResourceController@show');
                    $api->get('reviews', 'ResourceController@getReviews');
                });
            });
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'PartnerJobController@index');

                $api->group(['prefix' => '{job}', 'middleware' => ['partner_job.auth']], function ($api) {
                    $api->post('accept', 'PartnerJobController@acceptJobAndAssignResource');
                    $api->post('reject', 'PartnerJobController@declineJob');
                    $api->put('/', 'PartnerJobController@update');

                    $api->group(['prefix' => 'materials'], function ($api) {
                        $api->get('/', 'PartnerJobController@getMaterials');
                        $api->post('/', 'PartnerJobController@addMaterial');
                        $api->put('/', 'PartnerJobController@updateMaterial');
                    });
                });
            });
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->get('new', 'PartnerOrderController@newOrders');
                $api->get('/', 'PartnerOrderController@getOrders');

                $api->group(['prefix' => '{order}', 'middleware' => ['partner_order.auth']], function ($api) {
                    $api->get('/', 'PartnerOrderController@show');
                    $api->get('bills', 'PartnerOrderController@getBillsV1');
                    $api->get('logs', 'PartnerOrderController@getLogs');
                    $api->get('payments', 'PartnerOrderController@getPayments');
                    $api->post('comments', 'PartnerOrderController@postComment');
                });
            });
        });
        $api->group(['prefix' => 'resources/{resource}', 'middleware' => ['resource.auth']], function ($api) {
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'ResourceJobController@index');
                $api->group(['prefix' => '{job}', 'middleware' => ['resource_job.auth']], function ($api) {
                    $api->get('/', 'ResourceJobController@show');
                    $api->put('/', 'ResourceJobController@update');
                    $api->get('others', 'ResourceJobController@otherJobs');
                    $api->post('payment', 'ResourceJobController@collect');
                });
            });
            $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->get('/', 'ResourceJobController@index');
            });
        });
        $api->group(['prefix' => 'affiliate/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->post('edit', 'AffiliateController@edit');
            $api->post('update-profile-picture', 'AffiliateController@updateProfilePic');
            $api->get('lead-info', 'AffiliateController@leadInfo');

            $api->get('wallet', 'AffiliateController@getWallet');
            $api->get('status', 'AffiliateController@getStatus');
            $api->get('affiliations', 'AffiliationController@index');
            $api->post('affiliations', 'AffiliationController@create');
        });
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->post('edit', 'AffiliateController@edit');
            $api->get('leads', 'AffiliateController@leadInfo');
            $api->get('notifications', 'AffiliateController@getNotifications');

            $api->get('wallet', 'AffiliateController@getWallet');
            $api->get('status', 'AffiliateController@getStatus');
            $api->get('affiliations', 'AffiliationController@newIndex');
            $api->post('affiliations', 'AffiliationController@create');
            $api->get('transactions', 'AffiliateController@getTransactions');

            $api->get('leaderboard', 'AffiliateController@getLeaderboard');
            $api->group(['prefix' => 'ambassador'], function ($api) {
                $api->get('/', 'AffiliateController@getGodFather');
                $api->get('code', 'AffiliateController@getAmbassador');
                $api->post('code', 'AffiliateController@joinClan');
                $api->get('agents', 'AffiliateController@getAgents');
                $api->get('summary', 'AffiliateController@getAmbassadorSummary');
            });
        });
        $api->group(['prefix' => 'profile', 'middleware' => ['profile.auth']], function ($api) {
            $api->post('change-picture', 'ProfileController@changePicture');
        });

    });
    $api->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers'], function ($api) {
        $api->get('locations','LocationController@index');
        $api->post('service-requests', 'ServiceRequestController@store');
        $api->post('password/email', 'Auth\PasswordController@sendResetPasswordEmail');
        $api->post('password/validate', 'Auth\PasswordController@validatePasswordResetCode');
        $api->post('password/reset', 'Auth\PasswordController@reset');
        $api->group(['prefix' => 'orders'], function ($api) {
            $api->get('online', 'OrderController@clearPayment');
            $api->group(['prefix' => 'payments'], function ($api) {
                $api->post('success', 'OnlinePaymentController@success');
                $api->post('fail', 'OnlinePaymentController@fail');
                $api->post('cancel', 'OnlinePaymentController@fail');
            });
        });
        $api->group(['prefix' => 'login'], function ($api) {
            $api->post('gmail', 'Auth\GoogleController@login');
        });
        $api->group(['prefix' => 'register'], function ($api) {
            $api->post('gmail', 'Auth\GoogleController@register');
            $api->post('kit/partner', 'Auth\PartnerRegistrationController@register');
        });
        $api->get('times', 'ScheduleTimeController@index');
        $api->get('settings', 'HomePageSettingController@index');
        $api->get('settings/car', 'HomePageSettingController@getCar');
        $api->get('home-grids', 'HomeGridController@index');
        $api->group(['prefix' => 'category-groups'], function ($api) {
            $api->get('', 'CategoryGroupController@index');
            $api->group(['prefix' => '{id}'], function ($api) {
                $api->get('', 'CategoryGroupController@show');
            });
        });
        $api->group(['prefix' => 'categories'], function ($api) {
            $api->group(['prefix' => '{id}'], function ($api) {
                $api->get('', 'CategoryController@show');
                $api->get('services', 'CategoryController@getServices');
                $api->get('reviews', 'CategoryController@getReviews');
                $api->get('locations/{location}/partners', 'CategoryController@getPartnersOfLocation');
            });
        });
        $api->group(['prefix' => 'locations'], function ($api) {
            $api->get('{location}/partners', 'PartnerController@findPartners');
            $api->get('current', 'LocationController@getCurrent');
        });
        $api->group(['prefix' => 'job_service'], function ($api) {
            $api->post('/', 'JobServiceController@store');
        });
        $api->group(['prefix' => 'partners'], function ($api) {
            $api->group(['prefix' => '{partner}'], function ($api) {
                $api->get('/', 'PartnerController@show');
                $api->get('categories/{category}/services', 'PartnerController@getServices');
            });
        });
        $api->group(['prefix' => 'customers'], function ($api) {
            $api->group(['prefix' => '{customer}', 'middleware' => ['customer.auth']], function ($api) {
                $api->get('checkout-info', 'CustomerController@getDeliveryInfo');
                $api->put('notifications', 'CustomerNotificationController@update');
                $api->group(['prefix' => 'favorites'], function ($api) {
                    $api->get('/', 'CustomerFavoriteController@index');
                    $api->post('/', 'CustomerFavoriteController@store');
                    $api->put('/', 'CustomerFavoriteController@update');
                    $api->delete('{favorite}', 'CustomerFavoriteController@destroy');
                });
                $api->group(['prefix' => 'promotions'], function ($api) {
                    $api->get('/', 'PromotionController@index');
                    $api->post('/', 'PromotionController@addPromo');
                });

                $api->group(['prefix' => 'delivery-addresses'], function ($api) {
                    $api->get('/', 'CustomerDeliveryAddressController@index');
                    $api->post('/', 'CustomerDeliveryAddressController@store');
                    $api->put('{delivery_address}', 'CustomerDeliveryAddressController@update');
                    $api->delete('{delivery_address}', 'CustomerDeliveryAddressController@destroy');
                });
                $api->group(['prefix' => 'orders'], function ($api) {
                    $api->post('/', 'OrderController@store');
                    $api->get('/', 'CustomerOrderController@index');
                    $api->get('valid', 'OrderController@checkOrderValidity');
                    $api->get('payment/valid', 'OrderController@checkInvoiceValidity');
                    $api->post('promotions', 'PromotionController@applyPromotion');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->get('/', 'CustomerOrderController@show');
                    });
                });
                $api->group(['prefix' => 'jobs'], function ($api) {
                    $api->get('/', 'JobController@index');
                    $api->group(['prefix' => '{job}', 'middleware' => ['customer_job.auth']], function ($api) {
                        $api->get('/', 'JobController@show');
                        $api->get('bills', 'JobController@getBills');
                        $api->get('bills/clear', 'JobController@clearBills');
                        $api->get('logs', 'JobController@getLogs');
                        $api->post('reviews', 'ReviewController@store');
                        $api->group(['prefix' => 'complains'], function ($api) {
                            $api->get('/', 'ComplainController@index');
                            $api->post('/', 'ComplainController@store');
                            $api->group(['prefix' => '{complain}'], function ($api) {
                                $api->post('/', 'ComplainController@postComment');
                                $api->get('/', 'ComplainController@show');
                            });
                        });
                        $api->group(['prefix' => 'rates'], function ($api) {
                            $api->get('/', 'RateController@index');
                            $api->post('/', 'RateController@store');
                        });
                    });
                });
            });
        });
        $api->group(['prefix' => 'resources/{resource}', 'middleware' => ['resource.auth']], function ($api) {
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->group(['prefix' => '{job}', 'middleware' => ['resource_job.auth']], function ($api) {
                    $api->get('bills', 'ResourceJobController@getBills');
                    $api->post('extends', 'ResourceScheduleController@extendTime');
                    $api->post('reviews', 'ResourceJobRateController@store');
                    $api->group(['prefix' => 'rates'], function ($api) {
                        $api->get('/', 'ResourceJobRateController@index');
                        $api->post('/', 'RateController@storeCustomerReview');
                    });
                });
            });
        });
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->get('operations', 'Partner\OperationController@index');
            $api->post('operations', 'Partner\OperationController@store');
            $api->post('categories', 'Partner\OperationController@saveCategories');
            $api->group(['prefix' => 'resources'], function ($api) {
                $api->post('/', 'Resource\PersonalInformationController@store');
                $api->group(['prefix' => '{resource}', 'middleware' => ['partner_resource.auth']], function ($api) {
                    $api->get('/', 'Resource\PersonalInformationController@index');
                    $api->post('/', 'Resource\PersonalInformationController@update');
                });
            });
            $api->get('completion', 'Partner\ProfileCompletionController@getProfileCompletion');
            $api->get('collections', 'PartnerOrderPaymentController@index');
            $api->get('training', 'PartnerTrainingController@redirect');
            $api->post('pay-sheba', 'PartnerTransactionController@payToSheba');
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->group(['prefix' => '{order}', 'middleware' => ['partner_order.auth']], function ($api) {
                    $api->get('/', 'PartnerOrderController@showV2');
                    $api->get('bills', 'PartnerOrderController@getBillsV2');
                    $api->post('services', 'PartnerOrderController@addService');
                });
            });
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->group(['prefix' => '{job}', 'middleware' => ['partner_job.auth']], function ($api) {
                    $api->put('/', 'PartnerJobController@update');

                    $api->group(['prefix' => 'materials'], function ($api) {
                        $api->get('/', 'PartnerJobController@getMaterials');
                    });
                });
            });
        });
    });

});
