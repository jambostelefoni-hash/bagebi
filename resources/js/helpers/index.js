const nottify = (event) => {
  event.preventDefault()
  const target = event.currentTarget
  const object = {
    message: 'ნამდვილად გსურთ წაშლა?',
    buttons: {
        cancel: {
            label: '<i class="fa fa-times"></i> გაუქმება'
        },
        confirm: {
            label: '<i class="fa fa-check"></i> დადასტურება'
        }
    },
    callback: function (result) {
      if (result && target.dataset.submit) document.querySelector(`#${target.dataset.submit}`).submit()
      else if (result) {
        const form = document.createElement('form')
        form.method = 'POST'
        form.action = target.dataset.href
        const csrf = document.querySelector('meta[name="csrf-token"]').content
        form.innerHTML = `<input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="_method" value="DELETE">`
        document.body.appendChild(form)
        form.submit()
      }
      return
    }
  }

  if (target.dataset.title) object.title = target.dataset.title
  if (target.dataset.message) object.message = target.dataset.message

  return !target.dataset.noButtons ? bootbox.confirm(object) : bootbox.alert(target.dataset.message)	
}

window.nottify = nottify
