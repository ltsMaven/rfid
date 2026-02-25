let port, writer, reader, readLoop;
// let readLoop;
let epcSet = new Set();
let tagCount = 0;
let loopint = 1000; // interval loop pengecekan data
const arrayData = [];
const arrayHandheld = [];

const tagList = document.getElementById("tampungan");

document.addEventListener("DOMContentLoaded", () => {

	const rfidInput = document.getElementById("inputrfid");
	rfidInput.addEventListener("input", simulateRFIDReading);
	rfidInput.addEventListener("input", validateContainer);


	var timerHandheld = setInterval(validateContainer, 2000);


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
		col2.className = "col-2 text-center";
		col2.id = `done-${key}`; // unique per-row ID
		row.appendChild(col2);

		// 3) OK/NG badge (initially empty)
		const col3 = document.createElement("div");
		col3.className = "col-3 d-flex justify-content-end align-items-end";
		col3.id = `badge-${key}`; // unique per-row ID
		row.appendChild(col3);

		tagList.append(row);
	}

	let urut = 0;
	function simulateRFIDReading() {
		const rfidData = document.getElementById("inputrfid").value;
		document.getElementById("inputrfid").value = "";

		if (arrayData.includes(rfidData)) {
		} else {
			arrayData.push(rfidData); // Menambahkan data ke array
			if (rfidData != "") {
				document.getElementById("posex").innerHTML = "Retrieve Data Tags ..";
				urut = urut + 1;
				databaru = 1;
				loopint = 2000;
				arr = {
					key: urut,
					value: rfidData,
				};
				arrayHandheld.push(arr);
				const text = urut.toLocaleString("id-ID"); // Format lokal Indonesia
				// textarea.value += text + ". " + rfidData + "\n"; // Menambahkan data RFID ke textarea
				// addTagRow(urut, rfidData);
				document.getElementById("posex").innerHTML = "Done ..";
				clearInterval(timerHandheld);
				timerHandheld = setInterval(validateContainer, 2000);
			}
		}
	}
	function validateContainer() {
		const plno = document.getElementById("plSelect").value;
		if (!plno || arrayHandheld.length === 0) return;

		// pull off the one you just scanned
		const latestScan = arrayHandheld.pop();

		const payload = {
			selectedPlNo: plno,
			"data[0][key]": latestScan.key,
			"data[0][value]": latestScan.value,
		};

		$.ajax({
			dataType: "json",
			type: "POST",
			url: base_url + "page/validateContainer",
			data: payload,
			success: function (results) {
				results.forEach((item) => {
					const row = document.getElementById(item.key);
					if (!row) return;

					const badgeCol = row.children[2];
					let color;
					if (item.status === "OK") {
						color = "success";
					} else if (item.status === "SA") {
						alert("Barang ini tidak ditemukan di gudang");
						return;
					} else {
						alert("Barang ini tidak ditemukan di order ini");
						return;
					}

					badgeCol.innerHTML = `<span class="badge bg-${color}">${item.status}</span>`;
				});
			},
			error(xhr, status, err) {
				console.error("validateContainer failed:", err);
			},
		});
	}
});
