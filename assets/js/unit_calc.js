
function unitcalc(obj){
	var fields = JSON.parse(obj[1]);

	var deb = $('#input'+fields[0]+obj[2]).val();
	var fin = $('#input'+fields[1]+obj[2]).val();
	var diff = get_time_diff(deb,fin);
	$('#input'+fields[2]+obj[2]).val(diff);
}

function get_time_diff( time1, time2 )
{
	var date1 = new Date("2022-09-10T"+time1);
	var date2 = new Date("2022-09-10T"+time2);
	
	if (date1 < date2) {
        var milisec_diff = date2 - date1;
    }else{
        var milisec_diff = date1 - date2;
    }

	let msec = milisec_diff;
	const hh = Math.floor(msec / 1000 / 60 / 60);
	msec -= hh * 1000 * 60 * 60;
	const mm = Math.floor(msec / 1000 / 60);
	msec -= mm * 1000 * 60;
	const ss = Math.floor(msec / 1000);
	msec -= ss * 1000;

    return hh+","+mm;
}


