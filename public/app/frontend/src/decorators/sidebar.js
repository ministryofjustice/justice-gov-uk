export default function (story) {
    const decorator = document.createElement('div');
    decorator.style.maxWidth = '24rem';
    decorator.style.minWidth = '18rem';
    decorator.innerHTML = story();
    return decorator;
}
