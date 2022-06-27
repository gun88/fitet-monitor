jQuery(function ($) {

	const autoCompleteJS = new autoComplete({
		selector: "#club-name-autocomplete",
		debounce: 300,
		threshold: 3,
		resultsList: {
			element: (list, data) => {
				const info = document.createElement("p");
				if (data.results.length > 0) {
					info.innerHTML = `Displaying <strong>${data.results.length}</strong> out of <strong>${data.matches.length}</strong> results`;
				} else {
					info.innerHTML = `Found <strong>${data.matches.length}</strong> matching results for <strong>"${data.query}"</strong>`;
				}
				list.prepend(info);
			},
			noResults: true,
			maxResults: 20,
			tabSelect: true
		},
		resultItem: {
			element: (item, data) => {
				// Modify Results Item Style
				item.style = "display: flex; justify-content: space-between;";
				// Modify Results Item Content
				item.innerHTML = `
<div class="fm-autocomplete-result"">
	<img class="fm-autocomplete-club-logo" src="${data.value['clubLogo']}"  alt="${data.value['clubName']}"
		onError="this.onerror=null;this.src='/wp-content/plugins/fitet-monitor/src/public/assets/fitet-monitor-no-logo.svg';">
	<div class="fm-autocomplete-club-name">${data.match}</div>
	<div class="fm-autocomplete-club-province">${data.value['clubProvince']}</div>
</div>
     `;
			},
			highlight: true
		},
		data: {
			keys: ["clubName"],
			src: async (query) => {
				return await new Promise(function (resolve, reject) {
					wp.apiRequest({
						path: '../?rest_route=/fitet-monitor/v1/portal/find-clubs',
						type: 'GET',
						data: {query: query}
					})
						.done(r => {
							resolve(r);
						})
						.fail(r => {
							reject(r);
						})
					;
				});
			},
		},
		query: (input) => {
			return input.trim();
		},
		events: {
			input: {
				selection: (event) => {
					autoCompleteJS.input.value = event.detail.selection.value['clubName'];
					const code = event.detail.selection.value['clubCode'];
					const name = event.detail.selection.value['clubName'];
					const province = event.detail.selection.value['clubProvince'];
					const clubLogo = 'http://portale.fitet.org/images/societa/' + code + '.jpg';
					// todo script localization
					const clubNoLogo = "http://localhost/wp-content/plugins/fitet-monitor/src/public/assets/fitet-monitor-no-logo.svg";

					$('#clubCodeSpan').text(code);
					$('#clubNameSpan').text(name);
					$('#clubProvinceSpan').text(province);
					$('.fm-add-club-content img').attr('src', clubLogo).error(function () {
						$(this).unbind("error").attr("src", clubNoLogo);
					});
					$("input[name*='clubCode']").val(code);
					$("input[name*='clubName']").val(name);
					$("input[name*='clubProvince']").val(province);
					$("input[name*='clubLogo']").val(clubLogo);

					$('#fm-club-page #submit').prop('disabled', !code);

				}
			}
		}

	});


})
;
