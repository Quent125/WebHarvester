/** @format */

export function useAuth() {
	const router = useRouter();
	const config = useRuntimeConfig();

	const isLoggedIn = useState('auth:loggedIn', () => false);
	const authChecked = useState('auth:checked', () => false);
	const user = useState('auth:user', () => null);

	const loginForm = ref({
		login: '',
		password: '',
		remember: false,
	});

	const nuxtApp = useNuxtApp();

	if (import.meta.client) {
		nuxtApp.hook('app:created', () => {
			const token = localStorage.getItem('access_token');
			const userStr = localStorage.getItem('user');

			if (token) {
				isLoggedIn.value = true;
				if (userStr) {
					try {
						user.value = JSON.parse(userStr);
					} catch (e) {
						console.error('無法解析用戶資料:', e);
					}
				}
			}

			authChecked.value = true;

			setTimeout(() => checkAuth(), 100);
		});
	}

	const fixAvatarUrl = (url) => {
		if (!url) return null;

		if (url.startsWith('http')) {
			return url;
		}

		let fixedUrl = url;

		if (fixedUrl.includes('/avatars/') && !fixedUrl.includes('/storage/')) {
			fixedUrl = fixedUrl.replace('/avatars/', '/storage/avatars/');
		}

		if (fixedUrl.startsWith('/')) {
			fixedUrl = 'http://localhost:8000' + fixedUrl;
		} else {
			fixedUrl = 'http://localhost:8000/' + fixedUrl;
		}

		return fixedUrl;
	};

	async function checkAuth() {
		if (authChecked.value && isLoggedIn.value) {
			if (isTokenExpired()) {
				console.log('Token 已過期，執行自動登出');
				handleTokenExpired();
				return false;
			}
			return await verifyTokenWithServer();
		}

		if (!import.meta.client) {
			authChecked.value = true;
			return isLoggedIn.value;
		}

		const token = localStorage.getItem('access_token');
		if (!token) {
			isLoggedIn.value = false;
			authChecked.value = true;
			return false;
		}

		if (isTokenExpired()) {
			console.log('Token 已過期，執行自動登出');
			handleTokenExpired();
			return false;
		}

		return await verifyTokenWithServer();
	}

	async function verifyTokenWithServer() {
		const token = localStorage.getItem('access_token');
		if (!token) {
			handleTokenExpired();
			return false;
		}

		try {
			const response = await $fetch(
				`${config.public.apiBase}/verify-access-token`,
				{
					method: 'POST',
					headers: { Authorization: `Bearer ${token}` },
				}
			);

			isLoggedIn.value = response.valid;

			if (response?.user) {
				user.value = response.user;
				if (user.value.avatar) {
					user.value.avatar = fixAvatarUrl(user.value.avatar);
				}
				localStorage.setItem('user', JSON.stringify(user.value));
			}

			if (!isLoggedIn.value) {
				handleTokenExpired();
				return false;
			}

			authChecked.value = true;
			return true;
		} catch (error) {
			console.error('Token 驗證失敗:', error);

			if (error.response?.status === 401) {
				console.log('Token 無效，執行登出');
				handleTokenExpired();
				return false;
			}

			handleTokenExpired();
			return false;
		}
	}

	function handleTokenExpired() {
		console.log('處理 Token 過期');

		clearAuth();

		isLoggedIn.value = false;
		authChecked.value = true;
	}

	function clearAuth() {
		if (import.meta.client) {
			localStorage.removeItem('access_token');
			localStorage.removeItem('user');
			localStorage.removeItem('token_expires_at');
		}
		isLoggedIn.value = false;
		user.value = null;
	}

	async function login(formData = null) {
		try {
			const loginData = formData || loginForm.value;

			const response = await $fetch(`${config.public.apiBase}/login`, {
				method: 'POST',
				body: loginData,
			});

			if (response.access_token) {
				if (import.meta.client) {
					localStorage.setItem('access_token', response.access_token);
					localStorage.setItem(
						'token_expires_at',
						response.expires_at
					);
					localStorage.setItem('user', JSON.stringify(response.user));
					localStorage.setItem(
						'hasFirescrawlApiKey',
						response.hasFirescrawlApiKey ? 'true' : 'false'
					);
				}

				isLoggedIn.value = true;
				user.value = response.user;
				authChecked.value = true;

				return true;
			}
			return false;
		} catch (error) {
			console.error('登入失敗:', error);
			throw error;
		}
	}

	async function logout() {
		if (!import.meta.client) return;

		const token = localStorage.getItem('access_token');

		try {
			if (token) {
				await $fetch(`${config.public.apiBase}/logout`, {
					method: 'POST',
					headers: {
						Authorization: `Bearer ${token}`,
					},
				});

				console.log('後端登出成功');
			}
		} catch (error) {
			console.error('登出時發生錯誤:', error);
		} finally {
			clearAuth();

			router.push('/auth');
		}
	}

	function isTokenExpired() {
		if (!import.meta.client) return false;

		const expiresAt = localStorage.getItem('token_expires_at');
		if (!expiresAt) {
			console.log('沒有找到過期時間，視為過期');
			return true;
		}

		const expireDate = new Date(expiresAt);
		const now = new Date();

		console.log('當前時間:', now.toISOString());
		console.log('過期時間:', expireDate.toISOString());

		if (isNaN(expireDate.getTime())) {
			console.log('過期時間格式無效，視為過期');
			return true;
		}

		// 提前 30 秒視為過期，避免在請求過程中過期
		const bufferTime = 30 * 1000; // 30 秒
		const isExpired = expireDate.getTime() - now.getTime() <= bufferTime;

		console.log('Token 是否過期:', isExpired);
		return isExpired;
	}

	function goToErrorPage() {
		router.push('/errorPage');
	}

	// 返回所有需要的狀態和方法
	return {
		// 狀態
		isLoggedIn,
		authChecked,
		user,
		loginForm,

		// 方法
		checkAuth,
		login,
		logout,
		handleTokenExpired,
		clearAuth,
		isTokenExpired,
		fixAvatarUrl,
		goToErrorPage,
	};
}
