(function( $ ) {
	'use strict';
	$(document).ready(function(){
		//Deleting a user
		$('a.delete-user').on('click', function(e){
			e.preventDefault();
			let url = $(this).attr('href')
			let user = $(this).attr('user')
			let formData = new FormData();
			formData.append('action', 'delete_user')
			formData.append('user', user)
			$.ajax({
				url:url,
				type: 'post',
				data:formData,
				processData: false,
				contentType: false,
				success: function(response){
					var res = JSON.parse(response)
					if(res['status']) {
						alert('User Deleted !!')
						location.reload()
					}
					else{
						alert('User couldn\'t be deleted !!')
					}
				},
				error: function(){
					alert('Error !!')
				}
			})
		})
		
		//Deleting user by admin
		$('.delete-user-admin').click(function(e){
			e.preventDefault()
			var url = e.target.href
			var par = $(this).parents('tr')
			let user = par.attr('user')
			var formData = new FormData()
			formData.append('action', 'my_plugin_delete_user_by_admin')
			formData.append('user', user)
			$.ajax({
				url:url,
				type: 'post',
				data:formData,
				processData: false,
				contentType: false,
				success: function(response){
					response=JSON.parse(response)
					if(response){
						par.remove()
						alert('User Deleted')
					}
				}
			})
		})
		//Updating User from admin profile frontend
		$('.registered-users input[type="submit"]').click(function(){
			let url = $(this).attr('update-url')
			let par = $(this).parents('tr')
			let id = $(this).attr('user')
			let name = $(par).find('input[name="name"]').val()
			let username = $(par).find('input[name="username"]').val()
			let email = $(par).find('input[name="email"]').val()
			let pass = $(par).find('input[name="password"]').val()
			
			var formData = new FormData()
			formData.append('action', 'update_user')
			formData.append('user', id)
			formData.append('name', name)
			formData.append('username', username)
			formData.append('email', email)
			formData.append('password', pass)
			$.ajax({
				url:url,
				type: 'post',
				data:formData,
				processData: false,
				contentType: false,
				success: function(response){
					var res = JSON.parse(response)
					if(res['status']) {
						alert('Updated !!')
						location.reload()
					}
					else{
						alert('User couldn\'t be updated !!')
					}
				},
				error: function(){
					alert('Error !!')
				}
			})
		})

		//Updating User from profile page
		$('#profile-update').submit(function(e){
			e.preventDefault()
			var phone = $(this).find('input[name="tel"]').val()
			var form = this
			var pristine = new Pristine(form);
			var valid = pristine.validate();
			if(valid && (/^\d+$/.test(phone)) && phone.length==10){
				let url = $(this).attr('update-url')
				let id = $(this).find('#user-info').attr('user-id')
				let current_user = $(this).find('#user-info').val()
				let username = $(this).find('#profile-username').val()
				var formData = new FormData(this)
				formData.append('action', 'update_user_profile')
				formData.append('user', id)
				$.ajax({
					url:url,
					type: 'post',
					data:formData,
					processData: false,
					contentType: false,
					success: function(response){
						response = JSON.parse(response)
						if(response['status']){
							if(current_user!=username){
								alert('Profile Updated. You will logged out as the username is changed.')
								location.reload()
							}
							else alert('Profile Updated.')
						}
						else{
							alert('There was an error updating the profile.')
						}
					},
					error: function(){
						alert('Can\'t Update.')
					}
				})
			}
			else{
				alert('Invalid Entries')
			}
		})		

		//User Login
		$('#login-formm').on('submit', function(e){
			e.preventDefault()
			var form = this
			var pristine = new Pristine(form);
			var valid = pristine.validate();
			if(valid){
				let url = $(this).attr('req')
				var username = $('#login-username').val()
				var password = $('#login-password').val()
				let formData = new FormData()
				formData.append('action', 'my_plugin_login')
				formData.append('username', username)
				formData.append('password', password)
				$.ajax({
					url:url,
					type: 'post',
					data:formData,
					processData: false,
					contentType: false,
					success: function(response){
						console.log(response)
					},
					error: function(){
						alert('Error')
					}
				})
			}
		})
		//Image upload dynamic
		$('#profile-img').on('change', function(event){
			const input = event.target;
			if (input.files && input.files[0]) {
				const reader = new FileReader();
				reader.onload = function(e) {
				const previewImage = document.getElementById('profile-pic');
				previewImage.src = e.target.result;
				}
				reader.readAsDataURL(input.files[0]);
			}
		})
		//Registration form ajax
		$('#registration-form').submit(function(e){
			e.preventDefault()
			var phone = $(this).find('input[name="tel"]').val()
			var form = this
			var pristine = new Pristine(form);
			var valid = pristine.validate();
			if(valid && (/^\d+$/.test(phone)) && phone.length==10){
				let url = $(this).attr('action')
				var formData = new FormData(this)
				formData.append('action', 'my_plugin_register')
				$.ajax({
					url:url,
					type: 'post',
					data:formData,
					processData: false,
					contentType: false,
					success: function(response){
						try{
							response=JSON.parse(response)	
							if(response['status']){
								alert('New User Created!')
								location.reload()
							}
							else{
								alert('Couldn\'t register: '+response['error'] )
							}
						}
						catch{
							console.log(response)
							alert('Unknown Error!')
						}
					}
				})
			}
			else alert('Invalid Entries')
		})

	
	})
})( jQuery );
