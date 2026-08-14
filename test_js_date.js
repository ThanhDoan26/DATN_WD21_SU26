const movieDuration = 120;
let hiddenStartInputValue = "";

function pad(value) {
    return String(value).padStart(2, '0');
}

function formatDatetimeLocal(date) {
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1);
    const day = pad(date.getDate());
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    const seconds = pad(date.getSeconds());
    return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
}

function parseDatetimeLocal(value) {
    if (!value || typeof value !== 'string') {
        return null;
    }

    const parts = value.split('T');
    if (parts.length !== 2) {
        return null;
    }

    const [datePart, timePart] = parts;
    const [year, month, day] = datePart.split('-').map(Number);
    const timeParts = timePart.split(':').map(Number);
    const hour = timeParts[0];
    const minute = timeParts[1];
    const second = timeParts.length > 2 ? timeParts[2] : 0;

    if ([year, month, day, hour, minute, second].some(v => Number.isNaN(v))) {
        return null;
    }

    return new Date(year, month - 1, day, hour, minute, second, 0);
}

function buildHiddenDatetime(dateValue, hourValue, minuteValue) {
    if (!dateValue || !hourValue || !minuteValue) {
        return '';
    }
    let date = new Date(`${dateValue}T00:00`);
    const hour = Number(hourValue);
    const minute = Number(minuteValue);

    if (hour === 24) {
        date.setDate(date.getDate() + 1);
        date.setHours(0, 0, 0, 0);
    } else {
        date.setHours(hour, minute, 0, 0);
    }

    return formatDatetimeLocal(date);
}

function computeExpectedEnd() {
    if (!hiddenStartInputValue || !movieDuration) {
        return '';
    }
    const startDateObject = parseDatetimeLocal(hiddenStartInputValue);
    const durationMinutes = movieDuration;
    if (!startDateObject || !durationMinutes || Number.isNaN(durationMinutes)) {
        return '';
    }
    const calculatedEnd = new Date(startDateObject.getTime() + (durationMinutes + 15) * 60 * 1000);
    return formatDatetimeLocal(calculatedEnd);
}

// User enters date
hiddenStartInputValue = buildHiddenDatetime("2026-08-14", "", "");
console.log("After date:", computeExpectedEnd());

// User enters hour
hiddenStartInputValue = buildHiddenDatetime("2026-08-14", "09", "");
console.log("After hour:", computeExpectedEnd());

// User enters minute
hiddenStartInputValue = buildHiddenDatetime("2026-08-14", "09", "00");
console.log("After minute:", computeExpectedEnd());

