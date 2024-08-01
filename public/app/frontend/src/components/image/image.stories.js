import image from './image.html.twig';

export default {
    title: 'Components/Image',
    parameters: {
        layout: 'centered',
    },
};

const Template = (args) => {
    return image(args);
};

export const Default = Template.bind({});
Default.args = {
    url: 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9',
    alt: 'A statue of the scales of justice',
};
