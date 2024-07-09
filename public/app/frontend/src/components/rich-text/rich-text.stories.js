import richText from './rich-text.html.twig';
import './index.js';

export default {
    title: 'Components/Rich text',
};

const Template = (args) => {
    return richText(args);
};

export const RichText = Template.bind({});
RichText.args = {
    content: `
    <h1>Rich text</h1>
    <h2>Headings</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas gravida venenatis velit commodo mattis. In lectus neque, malesuada ultricies hendrerit id, fermentum eget purus.</p>
    <h2>Header (h2)</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas gravida venenatis velit commodo mattis. In lectus neque, malesuada ultricies hendrerit id, fermentum eget purus.</p>
    <h3>Header (h3)</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas gravida venenatis velit commodo mattis. In lectus neque, malesuada ultricies hendrerit id, fermentum eget purus.</p>
    <h4>Header (h4)</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas gravida venenatis velit commodo mattis. In lectus neque, malesuada ultricies hendrerit id, fermentum eget purus.</p>
    <h5>Header (h5)</h5>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas gravida venenatis velit commodo mattis. In lectus neque, malesuada ultricies hendrerit id, fermentum eget purus.</p>
    <h6>Header (h6)</h6>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas gravida venenatis velit commodo mattis. In lectus neque, malesuada ultricies hendrerit id, fermentum eget purus.</p>
    <h2>Body</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <a href="#">Maecenas gravida</a> venenatis velit commodo mattis. In lectus neque, <i>malesuada ultricies hendrerit id</i>, fermentum eget purus.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Maecenas gravida</b> venenatis velit commodo mattis.</p>
        <p>
Lorem ipsum dolor sit amet, consectetur <strong>adipiscing elit</strong>. Donec imperdiet congue lacus vel interdum. Suspendisse varius posuere lectus <em>dapibus laoreet. Etiam pulvinar</em>, erat tincidunt varius fermentum, urna quam dictum ante, nec pellentesque mauris arcu sit amet ipsum. Nullam non ullamcorper orci, quis maximus ex. Integer vehicula justo a augue malesuada consequat. Nunc vel nunc varius, aliquam nibh quis, dignissim enim. Pellentesque quis molestie nisi. Etiam euismod accumsan posuere.</p>
    <h2>Lists</h2>
    <ol>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Lorem ipsum dolor sit amet</li>
    </ol>
    <ul>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Lorem ipsum dolor sit amet</li>
        <li>Lorem ipsum dolor sit amet</li>
    </ul>
    <h2>Tables</h2>
    <figure>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Number</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                    <a href="#">General</a>
                    </td>
                    <td>Para. 1</td>
                </tr>
                <tr>
                    <td>
                    <a href="#"> The Shorter Trials Scheme </a>
                    </td>
                    <td>
                    Para. 2
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="#">The Flexible Trials Scheme</a>
                        </td>
                        <td>Para .3</td>
                </tr>
            </tbody>
        </table>
    </figure>
    `,
};
