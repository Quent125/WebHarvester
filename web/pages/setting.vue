<!-- @format -->
<template>
	<ClientOnly>
		<div v-if="authChecked && isLoggedIn" class="settings-container">
			<h1 class="page-title">設定</h1>

			<div class="settings-layout">
				<div class="settings-sidebar">
					<div
						v-for="(section, index) in sections"
						:key="index"
						class="sidebar-item"
						:class="{ active: activeSection === index }"
						@click="activeSection = index"
					>
						{{ section.title }}
					</div>
				</div>

				<div class="settings-content">
					<div v-if="activeSection === 0" class="settings-section">
						<h2 class="section-title">個人資料</h2>
						<div class="form-group">
							<label for="avatar">頭像</label>
							<div class="avatar-upload">
								<div class="user-avatar">
									<img
										v-if="userAvatar"
										:src="userAvatar"
										alt="用戶頭像"
									/>
									<img
										v-else-if="displayAvatar"
										:src="displayAvatar"
										alt="用戶頭像"
									/>
									<div v-else class="avatar-placeholder">
										<span>{{ userInitial }}</span>
									</div>
								</div>
								<button
									class="secondary-button"
									@click="uploadUserAvatar"
								>
									上傳頭像
								</button>
							</div>
						</div>

						<div class="form-group">
							<label for="name">帳號</label>
							<input
								type="text"
								id="name"
								v-model="userData.name"
								placeholder="您的帳號"
							/>
						</div>

						<div class="form-group">
							<label for="email">電子郵件</label>
							<input
								type="email"
								id="email"
								v-model="userData.email"
								disabled
								class="disabled-input"
							/>
							<div class="field-note">
								若要更改電子郵件，請聯絡客服
							</div>
						</div>

						<button
							class="primary-button save-button"
							@click="saveUserProfile"
						>
							<span v-if="isLoading">儲存中...</span>
							<span v-else>儲存變更</span>
						</button>
					</div>

					<div v-if="activeSection === 1" class="settings-section">
						<h2 class="section-title">密碼與安全</h2>

						<div class="form-group">
							<label for="current-password">目前密碼</label>
							<input
								type="password"
								id="current-password"
								v-model="passwordData.currentPassword"
								placeholder="輸入目前密碼"
							/>
						</div>

						<div class="form-group">
							<label for="new-password">新密碼</label>
							<input
								type="password"
								id="new-password"
								v-model="passwordData.newPassword"
								placeholder="輸入新密碼"
							/>
						</div>

						<div class="form-group">
							<label for="confirm-password">確認新密碼</label>
							<input
								type="password"
								id="confirm-password"
								v-model="passwordData.confirmPassword"
								placeholder="再次輸入新密碼"
							/>
						</div>

						<button
							class="primary-button save-button"
							@click="changePassword"
							@click.prevent="logout"
						>
							<span v-if="isPasswordLoading">更新中...</span>
							<span v-else>更新密碼</span>
						</button>
					</div>

					<div v-if="activeSection === 2" class="settings-section">
						<h2 class="section-title">Firescrawl設定</h2>
						<div class="form-group">
							<label for="api-key">Firescrawl API</label>
							<input
								type="text"
								id="api-key"
								v-model="crawlSettings.apiKey"
								placeholder="TYPE YOUR API KEY HERE"
							/>
							<client-only>
								<div class="api-key-status">
									<span v-if="hasApiKey" class="status-set">
										✓ 已設置 apiKey
									</span>
									<span v-else class="status-not-set">
										✕ 尚未設置 apiKey
									</span>
								</div>
							</client-only>
						</div>
						<button
							class="primary-button save-button"
							@click="saveCrawlSettings"
						>
							<span v-if="isCrawlLoading">儲存中...</span>
							<span v-else>儲存設定</span>
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
import { ref, computed, onMounted } from 'vue';

const {
	authChecked,
	isLoggedIn,
	user,
	checkAuth,
	logout,
	goToErrorPage,
	fixAvatarUrl,
} = useAuth();
const config = useRuntimeConfig();

const sections = [
	{ title: '個人資料' },
	{ title: '密碼與安全' },
	{ title: 'Firescrawl設定' },
];

const activeSection = ref(0);
const isLoading = ref(false);
const isPasswordLoading = ref(false);
const isCrawlLoading = ref(false);

const userAvatar = ref(null);
const userInitial = computed(() => {
	return user.value?.name ? user.value.name.charAt(0).toUpperCase() : '?';
});
const displayAvatar = computed(() => {
	return user.value?.avatar ? fixAvatarUrl(user.value.avatar) : null;
});

const userData = ref({
	name: '',
	email: '',
});

const passwordData = ref({
	currentPassword: '',
	newPassword: '',
	confirmPassword: '',
});

const crawlSettings = ref({
	apiKey: '',
});

const selectedAvatarFile = ref(null);
const localAvatarPreview = ref(null);

const hasApiKey = ref(false);

async function saveUserProfile() {
	isLoading.value = true;

	try {
		if (selectedAvatarFile.value) {
			const formData = new FormData();
			formData.append('avatar', selectedAvatarFile.value);

			const avatarResponse = await $fetch(
				`${config.public.apiBase}/user/avatar`,
				{
					method: 'POST',
					headers: {
						Authorization: `Bearer ${localStorage.getItem(
							'access_token'
						)}`,
					},
					body: formData,
				}
			);

			if (avatarResponse.avatarUrl) {
				console.log(
					'從伺服器返回的頭像路徑:',
					avatarResponse.avatarUrl
				);

				const avatarUrl = fixAvatarUrl(avatarResponse.avatarUrl);

				console.log('最終使用的頭像路徑:', avatarUrl);
				userAvatar.value = avatarUrl;

				if (user.value) {
					user.value.avatar = avatarResponse.avatarUrl;
				}

				document.querySelectorAll('.user-avatar img').forEach((img) => {
					img.src = avatarUrl;
				});
			}

			selectedAvatarFile.value = null;
			localAvatarPreview.value = null;
		}

		await $fetch(`${config.public.apiBase}/user/profile`, {
			method: 'PUT',
			headers: {
				Authorization: `Bearer ${localStorage.getItem('access_token')}`,
			},
			body: {
				name: userData.value.name,
			},
		});

		if (user.value) {
			user.value.name = userData.value.name;
			localStorage.setItem('user', JSON.stringify(user.value));
		}

		userAvatar.value = null;

		alert('個人資料已更新');
	} catch (error) {
		console.error('更新個人資料失敗:', error);

		let errorMessage = '更新失敗，請稍後再試';

		if (error.response) {
			errorMessage = `伺服器錯誤 (${error.response.status})`;
		} else if (error.message) {
			errorMessage = error.message;
		}

		alert(errorMessage);

		if (user.value?.avatar) {
			userAvatar.value = fixAvatarUrl(user.value.avatar);
		} else {
			userAvatar.value = null;
		}
	} finally {
		isLoading.value = false;
	}
}

async function uploadUserAvatar() {
	const fileInput = document.createElement('input');
	fileInput.type = 'file';
	fileInput.accept = 'image/*';
	fileInput.style.display = 'none';

	fileInput.onchange = async (event) => {
		const file = event.target.files[0];
		if (!file) return;

		if (!file.type.match('image.*')) {
			alert('請選擇圖片文件');
			fixAvatarUrl;
			return;
		}

		if (file.size > 2 * 1024 * 1024) {
			alert('圖片大小不能超過 2MB');
			return;
		}

		selectedAvatarFile.value = file;

		const reader = new FileReader();
		reader.onload = (e) => {
			localAvatarPreview.value = e.target.result;
			userAvatar.value = e.target.result;
		};
		reader.readAsDataURL(file);
	};

	document.body.appendChild(fileInput);
	fileInput.click();
	document.body.removeChild(fileInput);
}

async function changePassword() {
	if (!passwordData.value.currentPassword) {
		alert('請輸入目前密碼');
		return;
	}

	if (passwordData.value.newPassword !== passwordData.value.confirmPassword) {
		alert('兩次輸入的新密碼不一致');
		return;
	}

	isPasswordLoading.value = true;

	try {
		const response = await $fetch(
			`${config.public.apiBase}/user/change-password`,
			{
				method: 'PUT',
				headers: {
					Authorization: `Bearer ${localStorage.getItem(
						'access_token'
					)}`,
				},
				body: {
					currentPassword: passwordData.value.currentPassword,
					newPassword: passwordData.value.newPassword,
				},
			}
		);

		if (response.success) {
			alert('密碼已成功更新');

			passwordData.value = {
				currentPassword: '',
				newPassword: '',
				confirmPassword: '',
			};
		} else {
			alert(response.message || '更新失敗，請稍後再試');
		}
	} catch (error) {
		console.error('更新密碼失敗:', error);

		let errorMessage = '更新失敗，請稍後再試';
		if (error.response?.status === 400) {
			errorMessage = '當前密碼不正確';
		} else if (error.message) {
			errorMessage = error.message;
		}

		alert(errorMessage);
	} finally {
		isPasswordLoading.value = false;
	}
}

async function saveCrawlSettings() {
	isCrawlLoading.value = true;

	try {
		const response = await $fetch(
			`${config.public.apiBase}/user/crawl-settings`,
			{
				method: 'PUT',
				headers: {
					Authorization: `Bearer ${localStorage.getItem(
						'access_token'
					)}`,
				},
				body: {
					apiKey: crawlSettings.value.apiKey,
				},
			}
		);

		if (response.success) {
			alert('爬蟲設定已更新');

			crawlSettings.value = {
				apiKey: '',
			};
		} else {
			alert(response.message || '更新失敗，請稍後再試');
		}
	} catch (error) {
		console.error('更新設置失敗:', error);

		let errorMessage = '更新失敗，請稍後再試';
		if (error.message) {
			errorMessage = error.message;
		}

		alert(errorMessage);
	} finally {
		isCrawlLoading.value = false;
	}
}

onMounted(async () => {
	await checkAuth();

	if (!isLoggedIn.value) {
		goToErrorPage();
		return;
	}

	if (user.value) {
		userData.value.name = user.value.name || '';
		userData.value.email = user.value.email || '';
	}

	if (process) {
		const apiKey = localStorage.getItem('hasFirescrawlApiKey');
		hasApiKey.value = !!apiKey;
		if (apiKey) {
			crawlSettings.value.apiKey = '••••••••••••••••';
		}
	}
});
</script>

<style scoped>
@import '@/assets/css/setting.css';
</style>
