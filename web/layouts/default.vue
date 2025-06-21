<!-- @format -->

<template>
	<div class="app-container">
		<header class="header">
			<div class="logo">
				<h1>
					<NuxtLink to="/" class="logo-link">WebHarvester</NuxtLink>
				</h1>
			</div>

			<div class="nav">
				<ClientOnly>
					<div
						v-if="isLoggedIn && authChecked"
						class="user-menu"
						@click="toggleDropdown"
						@blur="closeDropdown"
					>
						<div class="user-avatar">
							<img
								v-if="displayAvatar"
								:src="displayAvatar"
								alt="用戶頭像"
							/>
							<div v-else class="avatar-placeholder">
								<span>{{ userInitial }}</span>
							</div>
						</div>

						<div class="dropdown-menu" v-show="showDropdown">
							<div class="dropdown-arrow"></div>
							<ul>
								<li>
									<NuxtLink to="/scrawl">Go Scrawl</NuxtLink>
								</li>
								<li><NuxtLink to="/setting">設定</NuxtLink></li>
								<li class="divider"></li>
								<li>
									<a href="#" @click.prevent="logout">登出</a>
								</li>
							</ul>
						</div>
					</div>

					<button
						v-else
						class="login-button"
						@click="navigateTo('/auth')"
					>
						登入
					</button>
				</ClientOnly>
			</div>
		</header>

		<main class="main-content">
			<slot />
		</main>

		<footer class="footer">
			<p>&copy; WebHarvester. 保留所有權利。</p>
		</footer>
	</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const { isLoggedIn, authChecked, user, checkAuth, logout, fixAvatarUrl } =
	useAuth();
const userInitial = computed(() => {
	return user.value?.name ? user.value.name.charAt(0).toUpperCase() : '?';
});
const displayAvatar = computed(() => {
	return user.value?.avatar ? fixAvatarUrl(user.value.avatar) : null;
});
const showDropdown = ref(false);

// onMounted(async () => {
// 	await checkAuth();

// 	if (user.value?.avatar) {
// 		userAvatar.value = fixAvatarUrl(user.value.avatar);
// 	}
// });

onMounted(async () => {
	await checkAuth();
});

function toggleDropdown() {
	showDropdown.value = !showDropdown.value;
}

function closeDropdown() {
	setTimeout(() => {
		showDropdown.value = false;
	}, 200);
}
</script>

<style>
@import '@/assets/css/layouts/default.css';
</style>
