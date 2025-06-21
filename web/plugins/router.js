/** @format */

export default defineNuxtPlugin(() => {
	const { checkAuth } = useAuth();

	addRouteMiddleware(
		'global-auth',
		async (to, from) => {
			// 每次路由變化時檢查身份
			await checkAuth();
		},
		{ global: true }
	);
});
