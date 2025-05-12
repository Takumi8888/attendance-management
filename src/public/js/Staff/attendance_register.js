// 現在時刻
// 2桁表示（01）
function twoDigit(num) {
	let ret;
	if (num < 10)
		ret = "0" + num;
	else
		ret = num;
	return ret;
}
// 時刻のリアルタイム表示
function showClock() {
	const nowDateTime = new Date();

	const year = nowDateTime.getFullYear();
	const month = nowDateTime.getMonth()+1;
	const date = nowDateTime.getDate();
	const day = nowDateTime.getDay();
	const weekDay = ["日", "月", "火", "水", "木", "金", "土"];
	const workDay = year + "年" + month + "月" + date + "日 (" + weekDay[day] + ")";

	const hour = twoDigit(nowDateTime.getHours());
	const minute = twoDigit(nowDateTime.getMinutes());
	const workHours = hour + ":" + minute;

	document.getElementById("date").innerHTML = workDay;
	document.getElementById("time").innerHTML = workHours;
}
setInterval('showClock()', 1000);