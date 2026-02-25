// document.addEventListener('livewire:init', () => {
//   Livewire.on('showModal', ({ modalId }) => {
//     if (!modalId) return;
//     $('#' + modalId).modal('show');
//   });

//   Livewire.on('hideModal', ({ modalId }) => {
//     if (!modalId) return;
//     $('#' + modalId).modal('hide');
//   });
// });


//   Livewire.on('showModal', modalId => {
//       $('#' + modalId).modal('show');
//   });

//   Livewire.on('hideModal', modalId => {
//       $('#' + modalId).modal('hide');
//   });



document.addEventListener('livewire:init', () => {
  const show = (modalId) => {
    if (!modalId || !window.$) return;
    $('#' + modalId).modal('show');
  };

  const hide = (modalId) => {
    if (!modalId || !window.$) return;
    $('#' + modalId).modal('hide');
  };

  Livewire.on('modal:show', ({ modalId } = {}) => show(modalId));
  Livewire.on('modal:hide', ({ modalId } = {}) => hide(modalId));
});
