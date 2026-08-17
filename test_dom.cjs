const jsdom = require("jsdom");
const { JSDOM } = jsdom;
const fs = require("fs");

const bladeContent = fs.readFileSync("resources/views/admin/showtimes/create.blade.php", "utf8");

// Extract just the script part
const scriptMatch = bladeContent.match(/<script>([\s\S]*?)<\/script>/);
const scriptContent = scriptMatch ? scriptMatch[1] : "";

const dom = new JSDOM(`
<!DOCTYPE html>
<html>
<body>
    <select id="movie_id">
        <option value="">-- Chọn phim --</option>
        <option value="1" data-duration="120">Phim 1</option>
    </select>
    <select id="room_id">
        <option value="1">Room 1</option>
    </select>
    
    <input type="date" id="start_date" value="2026-08-14">
    <select id="start_hour"><option value="">Giờ</option><option value="09">09</option><option value="10">10</option></select>
    <select id="start_minute"><option value="00">00</option><option value="15">15</option></select>
    <span id="start_period"></span>
    <input type="hidden" id="start_time" value="">
    
    <input type="date" id="end_date" value="">
    <select id="end_hour"><option value="">Giờ</option><option value="11">11</option><option value="12">12</option></select>
    <select id="end_minute"><option value="00">00</option><option value="15">15</option></select>
    <span id="end_period"></span>
    <input type="hidden" id="end_time" value="">

    <div id="seat-map-wrapper"></div>
    <div id="ticket-prices-section"></div>
    <div id="seat-detail-card"></div>
    <form></form>
    <script>
        ${scriptContent}
    </script>
</body>
</html>
`, { runScripts: "dangerously" });

setTimeout(() => {
    try {
        const document = dom.window.document;
        
        console.log("Initial start_date:", document.getElementById("start_date").value);
        console.log("Initial end_date:", document.getElementById("end_date").value);
        
        // Emulate user selecting a movie
        const movieSelect = document.getElementById("movie_id");
        movieSelect.value = "1";
        movieSelect.dispatchEvent(new dom.window.Event("change"));
        
        // Emulate user selecting a start hour
        const startHour = document.getElementById("start_hour");
        startHour.value = "09";
        startHour.dispatchEvent(new dom.window.Event("change"));
        
        console.log("After hour change - start_time hidden:", document.getElementById("start_time").value);
        console.log("After hour change - end_time hidden:", document.getElementById("end_time").value);
        console.log("After hour change - end_date UI:", document.getElementById("end_date").value);
        console.log("After hour change - end_hour UI:", document.getElementById("end_hour").value);
        console.log("After hour change - end_minute UI:", document.getElementById("end_minute").value);

    } catch (e) {
        console.error("Error:", e);
    }
}, 1000);
