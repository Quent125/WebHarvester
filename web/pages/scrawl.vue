<!-- @format -->
<template>
	<ClientOnly>
		<div class="container">
			<div class="input-container">
				<h1>Firecrawl 網頁爬蟲</h1>
				<div class="input-group">
					<input
						v-model="url"
						type="url"
						class="url-input"
						placeholder="https://example.com"
						@keyup.enter="submitUrl"
						:disabled="isLoading"
					/>
					<button
						@click="submitUrl"
						class="submit-btn"
						:disabled="isLoading"
					>
						{{ isLoading ? '處理中...' : '開始爬蟲' }}
					</button>
				</div>

				<div class="simple-options">
					<h3>爬蟲設定</h3>
					<div class="options-section">
						<div class="option-group">
							<label>選擇輸出格式</label>
							<div class="checkbox-list">
								<div
									v-for="format in availableFormats"
									:key="format"
									class="checkbox-group"
								>
									<input
										type="checkbox"
										:id="`format-${format}`"
										:value="format"
										v-model="
											crawlOptions.scrapeOptions.formats
										"
										@change="
											handleFormatChange(format, $event)
										"
									/>
									<label :for="`format-${format}`">
										{{ formatLabels[format] }}
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>

				<p
					v-if="message"
					class="status-message"
					:class="{ error: hasError }"
				>
					{{ message }}
				</p>

				<div v-if="isLoading" class="loading-section">
					<div class="loading-spinner"></div>
					<p class="loading-text">爬蟲進行中，請稍候...</p>
				</div>

				<div v-if="crawlResults" class="results-container">
					<h2>爬蟲結果</h2>
					<div class="tab-content">
						<div
							v-if="activeTab === 'overview'"
							class="overview-tab"
						>
							<div class="info-card">
								<h3>網站基本資訊</h3>
								<div class="info-item">
									<strong>網址:</strong>
									{{ crawlResults.data?.metadata?.url }}
								</div>
								<div class="info-item">
									<strong>標題:</strong>
									{{ crawlResults.data?.metadata?.title }}
								</div>
								<div class="info-item">
									<strong>作者:</strong>
									{{ crawlResults.data?.metadata?.author }}
								</div>
								<div class="info-item">
									<strong>描述:</strong>
									{{
										crawlResults.data?.metadata?.description
									}}
								</div>
								<div class="info-item">
									<strong>關鍵字:</strong>
									{{ crawlResults.data?.metadata?.keywords }}
								</div>
								<div class="info-item">
									<strong>爬蟲耗時:</strong>
									{{
										crawlResults.data?.crawl_time ||
										crawlDuration
									}}
									秒
								</div>
							</div>

							<div class="screenshot-container">
								<h3>網頁截圖</h3>
								<img
									:src="crawlResults.data.screenshot"
									alt="網頁截圖"
									class="page-screenshot"
								/>
							</div>
						</div>
					</div>

					<div class="action-buttons">
						<div class="download-options">
							<select
								v-model="downloadFormat"
								class="download-format-select"
							>
								<option
									value="markdown"
									v-if="crawlResults.data.markdown"
								>
									Markdown 報告
								</option>
								<option
									value="html"
									v-if="crawlResults.data.html"
								>
									HTML 內容
								</option>
								<option
									value="rawHtml"
									v-if="crawlResults.data.rawHtml"
								>
									原始 HTML
								</option>
								<option
									value="links"
									v-if="crawlResults.data.links"
								>
									連結清單
								</option>
								<option
									value="json"
									v-if="crawlResults.data.json"
								>
									JSON 資料
								</option>
								<option value="full-json">
									完整結果 (JSON)
								</option>
							</select>
							<button
								class="action-btn download-btn"
								@click="downloadResults"
								:disabled="!canDownload"
							>
								下載報告
							</button>
						</div>
						<button
							class="action-btn new-crawl-btn"
							@click="resetForm"
						>
							新爬蟲
						</button>
					</div>
				</div>
			</div>
		</div>
		<template #fallback>
			<div class="loading-container">
				<div class="loading-spinner"></div>
				<p>載入中...</p>
			</div>
		</template>
	</ClientOnly>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const { isLoggedIn, authChecked, checkAuth, goToErrorPage } = useAuth();

// State
const url = ref('');
const message = ref('');
const hasError = ref(false);
const isLoading = ref(false);
const crawlResults = ref(null);
const activeTab = ref('overview');
const pageSearch = ref('');
const mediaFilter = ref('all');
const crawlStartTime = ref(0);
const crawlEndTime = ref(0);
const crawlDuration = computed(() => {
	if (crawlStartTime.value === 0 || crawlEndTime.value === 0) return 0;
	return ((crawlEndTime.value - crawlStartTime.value) / 1000).toFixed(2);
});

const crawlOptions = ref({
	scrapeOptions: {
		formats: ['markdown'],
		onlyMainContent: true,
		screenshot: true,

		extractOptions: {
			links: true,
			images: true,
			headings: true,
		},
		jsonOptions: {
			compact: false,
			includeMetadata: true,
		},
	},
});

const config = useRuntimeConfig();

const downloadFormat = ref('full-json');

const canDownload = computed(() => {
	if (!crawlResults.value) return false;
	return Object.keys(crawlResults.value).length > 0;
});

const availableFormats = ['markdown', 'html', 'rawHtml', 'links'];

const formatLabels = {
	markdown: 'Markdown',
	html: 'HTML',
	rawHtml: 'rawHTML',
	links: '連結',
};

function handleFormatChange(format, event) {
	if (event.target.checked) {
		if (
			format === 'extract' &&
			!crawlOptions.value.scrapeOptions.extractOptions
		) {
			crawlOptions.value.scrapeOptions.extractOptions = {
				links: true,
				images: true,
				headings: true,
			};
		}

		if (
			format === 'json' &&
			!crawlOptions.value.scrapeOptions.jsonOptions
		) {
			crawlOptions.value.scrapeOptions.jsonOptions = {
				compact: false,
				includeMetadata: true,
			};
		}
	}
}

async function submitUrl() {
	if (!url.value) {
		setMessage('請輸入有效的網址', true);
		return;
	}

	try {
		new URL(url.value);
		isLoading.value = true;
		crawlResults.value = null;

		// 記錄開始時間
		crawlStartTime.value = Date.now();

		try {
			const response = await fetchCrawlResults();
			console.log('爬蟲回應:', response);

			// 記錄結束時間
			crawlEndTime.value = Date.now();

			if (response.success) {
				// 爬蟲耗時
				if (response.data) {
					response.data.crawl_time = crawlDuration.value;
				}
				processCrawlResults(response.data);
			} else {
				throw new Error(response.message || 'FireCrawl 爬蟲失敗');
			}
		} catch (error) {
			// 發生錯誤時也記錄結束時間
			crawlEndTime.value = Date.now();

			console.error('爬蟲請求失敗:', error);

			let errorMsg = '伺服器錯誤';
			if (error.response?._data?.message) {
				errorMsg = error.response._data.message;
			} else if (error.data?.message) {
				errorMsg = error.data.message;
			} else if (error.message) {
				errorMsg = error.message;
			}

			setMessage(`爬蟲失敗: ${errorMsg}`, true);
		} finally {
			isLoading.value = false;
		}
	} catch (e) {
		setMessage('請輸入有效的網址格式', true);
	}
}

async function fetchCrawlResults() {
	const token = localStorage.getItem('access_token');

	let formats = [...crawlOptions.value.scrapeOptions.formats];

	console.log('發送前的格式:', formats);

	const requestBody = {
		url: url.value,
		formats: formats,
		onlyMainContent: crawlOptions.value.scrapeOptions.onlyMainContent,
	};

	if (
		crawlOptions.value.scrapeOptions.screenshot &&
		!requestBody.formats.includes('screenshot') &&
		!requestBody.formats.includes('screenshot@fullPage')
	) {
		requestBody.formats.push('screenshot');
	}

	console.log('爬蟲選項:', crawlOptions.value);
	console.log('實際發送的請求:', requestBody);

	try {
		const response = await $fetch(`${config.public.apiBase}/scrape`, {
			method: 'POST',
			body: requestBody,
			headers: {
				Authorization: `Bearer ${token}`,
				'Content-Type': 'application/json',
			},
		});

		console.log('API 回應:', response);
		if (response.data) {
			console.log('回應中的格式:', Object.keys(response.data));
		}

		return response;
	} catch (error) {
		console.error('請求失敗詳情:', error);
		throw error;
	}
}

function processCrawlResults(data) {
	console.log('處理爬蟲結果:', data);

	// 保存完整結果
	crawlResults.value = data;

	// 檢查數據結構
	if (crawlResults.value.data) {
		console.log(
			'找到數據屬性，包含:',
			Object.keys(crawlResults.value.data)
		);
	}
}

function setMessage(text, isError) {
	message.value = text;
	hasError.value = isError;
}

function downloadResults() {
	if (!crawlResults.value) {
		setMessage('沒有可供下載的內容', true);
		return;
	}

	// 調試輸出
	console.log('爬蟲結果結構:', crawlResults.value);
	console.log('下載格式:', downloadFormat.value);

	const extractedData = extractFormatData(
		downloadFormat.value,
		crawlResults.value
	);

	if (extractedData.success) {
		downloadFile(
			extractedData.content,
			extractedData.mimeType,
			extractedData.filename
		);
	} else {
		setMessage(extractedData.error || '無法提取所選格式的數據', true);
	}
}

function extractFormatData(format, data) {
	console.log(`嘗試 ${format} 格式數據`);
	console.log('資料結構:', Object.keys(data));

	let contentData = data;
	if (data.data) {
		contentData = data.data;
		console.log('使用 data 屬性中的內容');
	}

	switch (format) {
		case 'markdown':
			if (contentData.markdown) {
				return {
					success: true,
					content: contentData.markdown,
					mimeType: 'text/markdown',
					filename: 'firecrawl-report.md',
				};
			}

		case 'html':
			if (contentData.html) {
				return {
					success: true,
					content: contentData.html,
					mimeType: 'text/html',
					filename: 'firecrawl-content.html',
				};
			}
			return { success: false, error: 'HTML 格式數據不可用' };

		case 'rawHtml':
			if (contentData.rawHtml) {
				return {
					success: true,
					content: contentData.rawHtml,
					mimeType: 'text/rawHtml',
					filename: 'firecrawl-content.html',
				};
			}
			return { success: false, error: 'HTML 格式數據不可用' };

		case 'links':
			if (contentData.links) {
				let linksContent;
				if (Array.isArray(contentData.links)) {
					linksContent = contentData.links.join('\n');
				} else if (typeof contentData.links === 'object') {
					linksContent = JSON.stringify(contentData.links, null, 2);
				} else {
					linksContent = String(contentData.links);
				}

				return {
					success: true,
					content: linksContent,
					mimeType: 'text/plain',
					filename: 'firecrawl-links.txt',
				};
			}
			return { success: false, error: '連結清單不可用' };

		case 'metadata':
			if (contentData.metadata) {
				return {
					success: true,
					content: JSON.stringify(contentData.metadata, null, 2),
					mimeType: 'application/json',
					filename: 'firecrawl-metadata.json',
				};
			}
			return { success: false, error: '元數據不可用' };

		case 'full-json':
		default:
			return {
				success: true,
				content: JSON.stringify(data, null, 2),
				mimeType: 'application/json',
				filename: 'firecrawl-full-report.json',
			};
	}
}

function downloadFile(content, mimeType, filename, isBase64 = false) {
	try {
		console.log(`準備下載 ${filename}，內容類型: ${mimeType}`);

		if (
			isBase64 &&
			typeof content === 'string' &&
			content.startsWith('data:')
		) {
			const link = document.createElement('a');
			link.href = content;
			link.download = filename;
			document.body.appendChild(link);
			link.click();

			setTimeout(() => {
				document.body.removeChild(link);
			}, 100);
		} else {
			const blob = new Blob([content], { type: mimeType });
			const url = URL.createObjectURL(blob);

			const link = document.createElement('a');
			link.href = url;
			link.download = filename;
			document.body.appendChild(link);
			link.click();

			setTimeout(() => {
				document.body.removeChild(link);
				URL.revokeObjectURL(url);
			}, 100);
		}
	} catch (error) {
		console.error('下載失敗:', error);
		setMessage('檔案下載失敗', true);
	}
}

function resetForm() {
	url.value = '';
	message.value = '';
	crawlResults.value = null;
	activeTab.value = 'overview';
	pageSearch.value = '';
	mediaFilter.value = 'all';

	crawlStartTime.value = 0;
	crawlEndTime.value = 0;

	crawlOptions.value = {
		scrapeOptions: {
			formats: ['markdown'],
			onlyMainContent: true,
			screenshot: false,

			extractOptions: {
				links: true,
				images: true,
				headings: true,
			},
			jsonOptions: {
				compact: false,
				includeMetadata: true,
			},
		},
	};
}

onMounted(async () => {
	console.log('scrawl 頁面載入');
	console.log(
		'初始狀態 - authChecked:',
		authChecked.value,
		'isLoggedIn:',
		isLoggedIn.value
	);

	await checkAuth();

	if (!isLoggedIn.value) {
		goToErrorPage();
		return;
	}

	if (process) {
		const apiKey = localStorage.getItem('hasFirescrawlApiKey');
		if (!apiKey) {
			console.warn('警告: 尚未設置 FireCrawl API Key');
			setMessage('警告: 請先在設置頁面配置 FireCrawl API Key', true);
		}
	}
});
</script>

<style scoped>
@import '@/assets/css/scrawl.css';
</style>
