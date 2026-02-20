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

    Livewire.on('showModal', ({ modalId }) => {
        if (!modalId) return;
        $('#' + modalId).modal('show');
    });

    Livewire.on('hideModal', ({ modalId }) => {
        if (!modalId) return;
        $('#' + modalId).modal('hide');
    });

});
