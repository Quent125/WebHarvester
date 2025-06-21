<!-- @format -->

<template>
	<div class="container">
		<h1>API 連接測試</h1>
		<div class="button-group">
			<button @click="testConnection" class="test-btn">測試連接</button>
			<button @click="refreshPage" class="refresh-btn">
				重新整理頁面
			</button>
		</div>

		<div v-if="loading" class="status loading">正在連接...</div>
		<div v-else-if="error" class="status error">連接失敗：{{ error }}</div>
		<div v-else-if="data" class="status success">
			<h2>連接成功！</h2>
			<pre>{{ JSON.stringify(data, null, 2) }}</pre>
		</div>
	</div>
</template>

<script setup>
const config = useRuntimeConfig();
const { data, error, loading, refresh } = useLazyFetch(
	() => `${config.public.apiBase}/test-connection`,
	{
		immediate: false,
	}
);

async function testConnection() {
	try {
		await refresh();
	} catch (e) {
		console.error('API 連接錯誤:', e);
	}
}

function refreshPage() {
	window.location.reload();
}
</script>

<style scoped>
.container {
	max-width: 800px;
	margin: 0 auto;
	padding: 20px;
	font-family: sans-serif;
}
.button-group {
	display: flex;
	gap: 10px;
	margin-bottom: 15px;
}
.test-btn,
.refresh-btn {
	color: white;
	padding: 10px 15px;
	border: none;
	border-radius: 4px;
	cursor: pointer;
}
.test-btn {
	background-color: #4caf50;
}

.refresh-btn {
	background-color: #2196f3;
}
.status {
	margin-top: 20px;
	padding: 15px;
	border-radius: 4px;
}
.loading {
	background-color: #f9f9f9;
}
.error {
	background-color: #ffebee;
	color: #c62828;
}
.success {
	background-color: #e8f5e9;
	color: #2e7d32;
}
pre {
	background-color: #f5f5f5;
	padding: 10px;
	border-radius: 4px;
	overflow-x: auto;
}
</style>
