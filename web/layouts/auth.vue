<!-- @format -->

<template>
	<div class="app-container">
		<header class="header">
			<div class="logo">
				<h1>
					<NuxtLink to="/" class="logo-link">WebHarvester</NuxtLink>
				</h1>
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

<script setup></script>

<style>
@import '@/assets/css/layouts/auth.css';
</style>
