let port, writer, reader, readLoop;
// let readLoop;
let epcSet = new Set();
let tagCount = 0;
let databarufix = 0;
let docek = 0; // Inisialisasi ada databaru
let loopint = 1000; // interval loop pengecekan data
let urut = 0; // Inisialisasi variabel urut
// const tagTable = document.getElementById("tagTable");
const arrayData = [];
const arrayHandheld = [];

const tagList = document.getElementById("tampungan");

// const connectBtn = document.getElementById("connectBtn");
const connectBtn = document.getElementById("button-connect2");
const clearBtn = document.getElementById("button-clear2");
const startBtn = document.getElementById("startBtn");
// const stopBtn = document.getElementById("stopBtn");
const powerSelect = document.getElementById("powerSelect");
const setPowerBtn = document.getElementById("button-setpower3");
const setAntennaBtn = document.getElementById("button-ant2");

// for (let i = 1; i <= 30; i++) {
// 	const opt = document.createElement("option");
// 	opt.value = i;
// 	opt.textContent = `${i} dBm`;
// 	powerSelect.appendChild(opt);
// }

function hexToBytes(hex) {
	return new Uint8Array(hex.match(/.{1,2}/g).map((b) => parseInt(b, 16)));
}

function crc16(buf) {
	let crc = 0xffff;
	for (let b of buf) {
		crc ^= b;
		for (let i = 0; i < 8; i++) {
			if (crc & 1) crc = (crc >> 1) ^ 0x8408;
			else crc >>= 1;
		}
	}
	return new Uint8Array([crc & 0xff, (crc >> 8) & 0xff]);
}

function sendCommand(cmdHex) {
	const cmd = hexToBytes(cmdHex);
	const crc = crc16(cmd);
	const full = new Uint8Array([...cmd, ...crc]);
	writer.write(full);
}

function requestCurrentPower() {
	sendCommand("040021"); // Get reader info (includes power in response)
}

connectBtn.onclick = async () => {
	// startBtn.onclick();
	if($("#plselect").val()==''){
		alert('Pilih Dahulu Packing List');
		return false;
	}
	try {
		port = await navigator.serial.requestPort();
		await port.open({ baudRate: 57600 });
		writer = port.writable.getWriter();
		reader = port.readable.getReader();
		sendCommand("05007600");
		// sendCommand("05007601");

		// stopBtn.disabled = false;
		// startBtn.disabled = false;
		// setPowerBtn.disabled = false;
		// setAntennaBtn.disabled = false;

		connectBtn.classList.add("hilang");

		startBtn.classList.remove("hilang");
		setPowerBtn.classList.remove("hilang");
		setAntennaBtn.classList.remove("hilang");
		clearBtn.classList.remove("hilang");
		setTimeout(requestCurrentPower, 300);

		readLoop = readFromPort();
	} catch (err) {
		alert("Gagal koneksi ke RFID: " + err);
		console.error(err);
	}
};
$("#button-clearx").on("click", function () {
	$("#tampungan").html('');
	arrayData.length = 0;
	epcSet.clear();
	urut = 0;
	// alert(epcSet.size);
	// epcSet.clear();
	// tagCount = 0;
	// databarufix = 0;
	// alert(epcSet.size);
	// docek = 0; // Inisialisasi ada databaru
	// loopint = 1000; // interval loop pengecekan data
});

startBtn.onclick = () => {
	// const mode = document.getElementById("modeSelect").value;
	// sendCommand(`050076${mode}`); // Set real-time inventory mode
	sendCommand("05007601");
	setPowerBtn.setAttribute("disabled", true);
	setAntennaBtn.setAttribute("disabled", true);
	startBtn.setAttribute("disabled", true);
	document.getElementById("posex").innerHTML = "Start Collect Data ... ";
};

// function startBtn() {
// 	// const mode = document.getElementById("modeSelect").value;
// 	// sendCommand(`050076${mode}`); // Set real-time inventory mode
// 	sendCommand("05007601");
// }

clearBtn.onclick = async () => {
	try {
		sendCommand("05007600"); // Stop inventory by switching to Answer Mode
		setPowerBtn.disabled = false;
		setAntennaBtn.disabled = false;
		startBtn.disabled = false;
		document.getElementById("posex").innerHTML = "Stop Collect Data ... ";
		if (reader) await reader.cancel();
	} catch (e) {
		console.warn("❌ Gagal stop inventory:", e);
	}
};

// async function stopBtn() {
// 	try {
// 		sendCommand("05007600"); // Stop inventory by switching to Answer Mode
// 		if (reader) await reader.cancel();
// 	} catch (e) {
// 		console.warn("❌ Gagal stop inventory:", e);
// 	}
// }

setPowerBtn.onclick = () => {
	const val = parseInt(powerSelect.value);
	const hex = val.toString(16).padStart(2, "0");
	sendCommand(`05002F${hex}`); // Set RF power
	document.getElementById("posex").innerHTML = "Set Power Saved";
};

// function setPowerBtn() {
// 	const val = parseInt(powerSelect.value);
// 	const hex = val.toString(16).padStart(2, "0");
// 	sendCommand(`05002F${hex}`); // Set RF power
// }

setAntennaBtn.onclick = () => {
	const value = [...document.querySelectorAll(".antenna")].reduce(
		(sum, cb, i) => sum | (cb.checked ? 1 << i : 0),
		0
	);
	if (value === 0) {
		alert("❌ Pilih minimal satu antena.");
		return;
	}
	const hex = value.toString(16).padStart(2, "0");
	const antennaByte = value & 0x0f;
	sendCommand(`05003F${antennaByte.toString(16).padStart(2, "0")}`);
	document.getElementById("posex").innerHTML = "Set Antena Saved";
};

// function setAntennaBtn() {
// 	const value = [...document.querySelectorAll(".antenna")].reduce(
// 		(sum, cb, i) => sum | (cb.checked ? 1 << i : 0),
// 		0
// 	);
// 	if (value === 0) {
// 		alert("❌ Pilih minimal satu antena.");
// 		return;
// 	}
// 	const hex = value.toString(16).padStart(2, "0");
// 	const antennaByte = value & 0x0f;
// 	sendCommand(`05003F${antennaByte.toString(16).padStart(2, "0")}`);
// }

async function readFromPort() {
	let buffer = [];
	try {
		while (true) {
			const { value, done } = await reader.read();
			if (done) break;
			if (value) {
				buffer.push(...value);
				while (buffer.length >= 5) {
					const len = buffer[0];
					if (buffer.length < len + 1) break;
					const frame = buffer.slice(0, len + 1);
					handleResponse(new Uint8Array(frame));
					buffer = buffer.slice(len + 1);
				}
			}
		}
	} catch (err) {
		console.error("Read error", err);
	}
}

var timerFixreader = setInterval(validateFixreader, 2000);

function handleResponse(data) {
	// console.log("RX:", data);

	// Handle reader info response (0x21)
	if (data.length >= 13 && data[2] === 0x21) {
		const power = data[10];
		const antennaMask = data[12];
		if (power >= 1 && power <= 30) {
			powerSelect.value = power;
		} else {
			console.warn("Invalid power value:", power, "- retrying");
			setTimeout(requestCurrentPower, 500);
		}

		// Set antenna checkboxes
		document.querySelectorAll(".antenna").forEach((chk, i) => {
			chk.checked = (antennaMask & (1 << i)) !== 0;
		});

		return;
	}

	if (data.length >= 7 && data[2] === 0xee && data[3] === 0x00) {
		const ant = data[4];
		const len = data[5];
		const epcBytes = data.slice(6, 6 + len);
		const rssi = data[6 + len];

		const epcStr = Array.from(epcBytes)
			.map((b) => b.toString(16).padStart(2, "0"))
			.join("")
			.toUpperCase();
		const rssiDbm = rssi - 130;
		const antNum = Math.log2(ant & 0x0f) + 1;

		if (!epcSet.has(epcStr)) {
			epcSet.add(epcStr);
			tagCount++;
			// const row = document.createElement("tr");
			// row.innerHTML = `<td>${tagCount}</td><td>${epcStr}</td><td>${rssiDbm}</td><td>${antNum}</td>`;
			// tagTable.insertBefore(row, tagTable.firstChild);
			// alert(epcStr);
			document.getElementById("posex").innerHTML = "Retrieve Data Tags ..";
			databarufix = 1;
			loopint = 2000;
			arr = {
				key: tagCount,
				value: epcStr,
			};
			arrayHandheld.push(arr);

			addTagRow(tagCount, epcStr)

			console.log("Tag baru ditemukan: " + epcStr);
			clearInterval(timerFixreader);
			timerFixreader = setInterval(validateFixreader, 2000);
		}
	}
}

/**
 * class buat validasi status
 */
function validateFixreader() {
	if (epcSet.size > 0 && databarufix == 1) {
		document.getElementById("posex").innerHTML = "Done..";
		console.log("Pengecekan Data");
		docek = 0;
		databarufix = 0;
		$.ajax({
			dataType: "json",
			type: "POST",
			url: base_url + "page/validateinbox",
			data: {
				data: arrayHandheld,
				plno: $("#plselect").val()
			},
			success: function(results) {
			results.forEach(item => {
				const { key, value: hasil, status: stat, done } = item;
				const row = document.getElementById(`${key}`);
				if (!row) return;

				row.children[0].textContent = `${key}. ${hasil}`;

				const colDone  = document.getElementById(`done-${key}`);
				colDone.textContent = done.trim();

				const colBadge = document.getElementById(`badge-${key}`);
				colBadge.innerHTML = "";
				const sp = document.createElement("span");
				let color;
				if (item.status === "OK") {
					color = "success";
				} else if (item.status === "SA") {
					color = "warning";
				} else {
					color = "danger";
				}	
				sp.className = `px-2 badge bg-${color}`;
				sp.textContent = stat;
				colBadge.appendChild(sp);
			});
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(xhr.status);
				console.log(thrownError);
			},
		});
	}
}

function addTagRow(key, text) {
	const row = document.createElement("div");
	row.className = "row align-items-center mb-1";
	row.id = `${key}`;

	// 1) INPUT LIST
	const col1 = document.createElement("div");
	col1.className = "col-7 text-start";
	col1.textContent = `${key}. ${text}`;
	row.appendChild(col1);

	// 2) STATUS MESSAGE (initially empty)
	const col2 = document.createElement("div");
	col2.className = "col-3 text-center font-11";
	col2.id = `done-${key}`; // unique per-row ID
	row.appendChild(col2);

	// 3) OK/NG badge (initially empty)
	const col3 = document.createElement("div");
	col3.className = "col-2 d-flex justify-content-end align-items-end";
	col3.id = `badge-${key}`; // unique per-row ID
	row.appendChild(col3);

	tagList.append(row);
}

function simulateRFIDReading() {
	const rfidData = document.getElementById("inputrfid").value;
	document.getElementById("inputrfid").value = "";

	if (arrayData.includes(rfidData)) {
	} else {
		arrayData.push(rfidData); // Menambahkan data ke array
		if (rfidData != "") {
			epcSet.add(rfidData);
			document.getElementById("posex").innerHTML = "Retrieve Data Tags ..";
			urut = urut + 1;
			databaru = 1;
			databarufix = 1;
			loopint = 2000;
			arr = {
				key: urut,
				value: rfidData,
			};
			arrayHandheld.push(arr);
			const text = urut.toLocaleString("id-ID"); // Format lokal Indonesia
			// textarea.value += text + ". " + rfidData + "\n"; // Menambahkan data RFID ke textarea

			addTagRow(urut, rfidData);
			document.getElementById("posex").innerHTML = "Done ..";
			clearInterval(timerFixreader);
			timerFixreader = setInterval(validateFixreader, 2000);
		}
	}
}
