import imageWithText from './image-with-text.html.twig';
export default {
    title: 'Components/Image with text',
};

const Template = (args) => {
    return imageWithText(args);
};

export const Left = Template.bind({});
Left.args = {
    direction: 'left',
    title: {
        text: 'Justice on GOV.UK',
        tag: 'h2',
        url: '#',
    },
    image: {
        url: 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9',
        alt: 'A statue of the scales of justice',
    },
    content: `<p>This website now hosts Civil and Family Procedure Committee Rules content only. You will be automatically redirected to equivalent content on <a href="#" target="_blank" rel="noopener">GOV.UK</a> or the National Archives.</p`,
};

export const Right = Template.bind({});
Right.args = {
    ...Left.args,
    direction: 'right',
};
